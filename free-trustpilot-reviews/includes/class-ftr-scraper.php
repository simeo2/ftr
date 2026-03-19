<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class FTR_Scraper {

    public static function init() {
        add_action( 'freetrustpilotreviews_daily_fetch_hook', array( __CLASS__, 'fetch' ) );
        add_filter( 'cron_schedules', array( __CLASS__, 'custom_cron_schedule' ) );
    }

    public static function custom_cron_schedule( $schedules ) {
        $hours = max( 1, (int) FTR_DB::get_setting( 'sync_hours', 24 ) ); 
        $schedules['freetrustpilotreviews_custom'] = array( 'interval' => $hours * 3600, 'display' => 'Every ' . $hours . ' Hours' );
        return $schedules;
    }

    public static function schedule_cron() {
        wp_clear_scheduled_hook( 'freetrustpilotreviews_daily_fetch_hook' );
        $hours = max( 1, (int) FTR_DB::get_setting( 'sync_hours', 24 ) );
        wp_schedule_event( time() + ( $hours * 3600 ), 'freetrustpilotreviews_custom', 'freetrustpilotreviews_daily_fetch_hook' );
    }

    // UPDATED: Now accepts dynamic 'from' and 'to' language codes
    private static function translate_text( $text, $from, $to ) {
        if ( empty($text) ) return '';
        if ( $from === $to && $from !== 'auto' ) return $text; // Skip if same language
        
        $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=' . urlencode($from) . '&tl=' . urlencode($to) . '&dt=t&q=' . urlencode($text);
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if ( is_wp_error($response) ) return $text;
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $translated = '';
        if ( isset($data[0]) && is_array($data[0]) ) {
            foreach ($data[0] as $chunk) { if ( isset($chunk[0]) ) $translated .= $chunk[0]; }
        }
        return !empty($translated) ? $translated : $text;
    }

    public static function fetch() {
        $metrics = array( 'http_status' => '', 'lock_hit' => 0, 'parsed_count' => 0, 'inserted_count' => 0, 'existing_count' => 0, 'updated_count' => 0, 'failed_count' => 0 );

        if ( get_transient( 'ftr_sync_lock' ) ) {
            $metrics['lock_hit'] = 1;
            FTR_DB::add_log('warning', 'Fetch aborted: Lock active.', $metrics);
            return array( 'success' => false, 'message' => 'Fetch already in progress. Locked.' );
        }
        set_transient( 'ftr_sync_lock', true, 5 * MINUTE_IN_SECONDS );

        $url = FTR_DB::get_setting( 'target_url', '' );
        if ( empty($url) ) {
            delete_transient( 'ftr_sync_lock' );
            $metrics['failed_count'] = 1;
            FTR_DB::add_log('error', 'No Trustpilot URL set.', $metrics);
            return array( 'success' => false, 'message' => 'No Trustpilot URL set.' );
        }
        
        $args = array(
            'headers' => array( 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0 Safari/537.36', 'Accept' => 'text/html,*/*;' ),
            'timeout' => 30
        );

        $response = wp_remote_get( $url, $args );
        
        if ( is_wp_error( $response ) ) {
            delete_transient( 'ftr_sync_lock' );
            $metrics['failed_count'] = 1;
            FTR_DB::add_log('error', 'WP Error: ' . $response->get_error_message(), $metrics);
            return array( 'success' => false, 'message' => 'WP Error: ' . $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $html = wp_remote_retrieve_body( $response );
        $metrics['http_status'] = $code;

        if ( $code !== 200 || strpos(strtolower($html), 'cloudflare') !== false ) {
            delete_transient( 'ftr_sync_lock' );
            $metrics['failed_count'] = 1;
            FTR_DB::add_log('error', 'Blocked by Cloudflare or HTTP Error.', $metrics);
            return array( 'success' => false, 'message' => 'Blocked by Trustpilot or HTTP Error.' );
        }

        $new_reviews = [];
        if ( preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/is', $html, $next_matches) ) {
            $next_data = json_decode($next_matches[1], true);
            if ( isset($next_data['props']['pageProps']['businessUnit']['displayName']) ) {
                FTR_DB::update_setting( 'business_name', sanitize_text_field($next_data['props']['pageProps']['businessUnit']['displayName']) );
            }
            if ( isset($next_data['props']['pageProps']['reviews']) && is_array($next_data['props']['pageProps']['reviews']) ) {
                foreach ( $next_data['props']['pageProps']['reviews'] as $rev ) {
                    $avatar = !empty($rev['consumer']['information']['pictureUrl']) ? $rev['consumer']['information']['pictureUrl'] : ($rev['consumer']['imageUrl'] ?? '');
                    $new_reviews[] = array(
                        'id'     => sanitize_text_field( $rev['id'] ?? md5(serialize($rev)) ),
                        'author' => sanitize_text_field( $rev['consumer']['displayName'] ?? 'Anonymous' ),
                        'avatar' => esc_url_raw( $avatar ),
                        'rating' => intval( $rev['rating'] ?? 0 ),
                        'text'   => sanitize_textarea_field( $rev['text'] ?? '' ),
                        'date'   => sanitize_text_field( $rev['dates']['publishedDate'] ?? date('c') )
                    );
                }
            }
        }

        $metrics['parsed_count'] = count($new_reviews);

        if ( empty($new_reviews) ) {
            delete_transient( 'ftr_sync_lock' );
            FTR_DB::add_log('warning', 'Parsed HTML successfully, but zero reviews were found in JSON.', $metrics);
            return array( 'success' => false, 'message' => 'No reviews found in the HTML.' );
        }

        usort($new_reviews, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        // Pull Translation Settings Before the Loop
        $enable_trans = FTR_DB::get_setting('enable_translation', '0');
        $trans_from   = FTR_DB::get_setting('translate_from', 'auto');
        $trans_to     = FTR_DB::get_setting('translate_to', 'en');

        global $wpdb;
        $table = $wpdb->prefix . 'ftr_reviews';

        $wpdb->query("START TRANSACTION");

        $max_id = (int) $wpdb->get_var("SELECT MAX(short_id) FROM $table FOR UPDATE");

        foreach ( $new_reviews as $rev ) {
            if ( !empty($rev['text']) ) {
                $exists = $wpdb->get_row( $wpdb->prepare("SELECT id, avatar FROM $table WHERE tp_id = %s", $rev['id']) );
                if ( $exists ) {
                    if ( empty($exists->avatar) && !empty($rev['avatar']) ) {
                        $updated = $wpdb->update( $table, array('avatar' => $rev['avatar']), array('id' => $exists->id) );
                        if ($updated !== false) {
                            $metrics['updated_count']++;
                        } else {
                            $metrics['failed_count']++;
                        }
                    } else {
                        $metrics['existing_count']++;
                    }
                } else {
                    $max_id++;
                    
                    // Conditionally Translate
                    if ( $enable_trans === '1' ) {
                        $front_end_text = self::translate_text($rev['text'], $trans_from, $trans_to);
                    } else {
                        $front_end_text = $rev['text']; // Fallback to raw English
                    }
                    
                    $inserted = $wpdb->insert( $table, array(
                        'tp_id'       => $rev['id'],
                        'short_id'    => $max_id,
                        'author'      => $rev['author'],
                        'avatar'      => $rev['avatar'],
                        'rating'      => $rev['rating'],
                        'review_text' => $rev['text'],
                        'review_text_tr' => $front_end_text, // Safely repurposed DB column
                        'review_date' => date('Y-m-d H:i:s', strtotime($rev['date']))
                    ));
                    
                    if ($inserted) {
                        $metrics['inserted_count']++;
                    } else {
                        $metrics['failed_count']++;
                    }
                }
            } else {
                $metrics['failed_count']++;
            }
        }

        $wpdb->query("COMMIT");
        delete_transient( 'ftr_sync_lock' );
        
        FTR_DB::update_setting('cache_version', time());
        FTR_DB::add_log('success', 'Fetch cycle completed.', $metrics);
        FTR_DB::prune_logs( 30 );

        return array( 'success' => true, 'message' => "Successfully fetched. Inserted " . $metrics['inserted_count'] . " new reviews." );
    }
}
