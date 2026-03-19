<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class FTR_DB {

    public static function activate() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $charset_collate = $wpdb->get_charset_collate();

        $table_reviews = $wpdb->prefix . 'ftr_reviews';
        dbDelta( "CREATE TABLE $table_reviews (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            short_id int(11) NOT NULL,
            tp_id varchar(100) NOT NULL,
            author varchar(255) NOT NULL,
            avatar text NOT NULL,
            rating tinyint(1) NOT NULL,
            review_text text NOT NULL,
            review_text_tr text NOT NULL,
            review_date datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY tp_id (tp_id)
        ) $charset_collate;" );

        $table_settings = $wpdb->prefix . 'ftr_settings';
        dbDelta( "CREATE TABLE $table_settings (
            setting_key varchar(100) NOT NULL,
            setting_value longtext NOT NULL,
            PRIMARY KEY  (setting_key)
        ) $charset_collate;" );

        $table_logs = $wpdb->prefix . 'ftr_logs';
        dbDelta( "CREATE TABLE $table_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            run_time datetime NOT NULL,
            status varchar(20) NOT NULL,
            message text NOT NULL,
            http_status varchar(10) NOT NULL DEFAULT '',
            lock_hit tinyint(1) NOT NULL DEFAULT 0,
            parsed_count int(11) NOT NULL DEFAULT 0,
            inserted_count int(11) NOT NULL DEFAULT 0,
            existing_count int(11) NOT NULL DEFAULT 0,
            updated_count int(11) NOT NULL DEFAULT 0,
            failed_count int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id)
        ) $charset_collate;" );

        self::update_setting('do_activation_redirect', '1');
        self::update_setting('sync_hours', '24');
        
        FTR_Scraper::schedule_cron();
    }

    public static function get_setting( $key, $default = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ftr_settings';
        if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ) return $default;
        $value = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM $table WHERE setting_key = %s", $key ) );
        return ( $value !== null ) ? $value : $default;
    }

    public static function update_setting( $key, $value ) {
        global $wpdb;
        $wpdb->replace( $wpdb->prefix . 'ftr_settings', array( 'setting_key' => $key, 'setting_value' => $value ), array( '%s', '%s' ) );
    }

    public static function delete_setting( $key ) {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'ftr_settings', array( 'setting_key' => $key ), array( '%s' ) );
    }

    public static function add_log( $status, $message, $metrics = array() ) {
        global $wpdb;
        $defaults = array(
            'http_status'    => '',
            'lock_hit'       => 0,
            'parsed_count'   => 0,
            'inserted_count' => 0,
            'existing_count' => 0,
            'updated_count'  => 0,
            'failed_count'   => 0
        );
        $m = wp_parse_args( $metrics, $defaults );

        $wpdb->insert( $wpdb->prefix . 'ftr_logs', array(
            'run_time'       => current_time('mysql'),
            'status'         => $status,
            'message'        => $message,
            'http_status'    => $m['http_status'],
            'lock_hit'       => $m['lock_hit'],
            'parsed_count'   => $m['parsed_count'],
            'inserted_count' => $m['inserted_count'],
            'existing_count' => $m['existing_count'],
            'updated_count'  => $m['updated_count'],
            'failed_count'   => $m['failed_count']
        ));
        
        self::update_setting('last_fetch_time', current_time('mysql'));
        if ($status === 'success') {
            self::update_setting('last_success_time', current_time('mysql'));
            $total = (int) self::get_setting('total_inserted', 0);
            self::update_setting('total_inserted', $total + $m['inserted_count']);
        } else {
            self::update_setting('last_error_message', $message);
        }
    }

    public static function get_logs( $limit = 100 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ftr_logs';
        if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ) return array();
        return $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table ORDER BY run_time DESC LIMIT %d", $limit), ARRAY_A );
    }

    public static function prune_logs( $days = 30 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ftr_logs';
        if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ) return;
        $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE run_time < DATE_SUB(NOW(), INTERVAL %d DAY)", $days ) );
    }

    public static function get_formatted_reviews( $args = array() ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ftr_reviews';
        if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ) return array();
        
        $where = "WHERE 1=1";
        
        if ( !empty($args['ids']) && is_array($args['ids']) ) {
            $ids = array_map('intval', $args['ids']);
            $where .= " AND short_id IN (" . implode(',', $ids) . ")";
        }
        
        if ( !empty($args['exclude_ids']) && is_array($args['exclude_ids']) ) {
            $no_ids = array_map('intval', $args['exclude_ids']);
            $where .= " AND short_id NOT IN (" . implode(',', $no_ids) . ")";
        }

        $limit_clause = "";
        if ( isset($args['limit']) && $args['limit'] > 0 ) {
            $limit_clause = $wpdb->prepare(" LIMIT %d", intval($args['limit']));
        }

        $query = "SELECT * FROM $table $where ORDER BY review_date DESC $limit_clause";
        $raw_reviews = $wpdb->get_results( $query, ARRAY_A );
        
        $formatted = array();
        foreach ( $raw_reviews as $r ) {
            $formatted[] = array(
                'id'       => $r['tp_id'],
                'short_id' => $r['short_id'],
                'author'   => $r['author'],
                'avatar'   => $r['avatar'],
                'rating'   => $r['rating'],
                'text'     => $r['review_text'],
                'text_tr'  => !empty($r['review_text_tr']) ? $r['review_text_tr'] : $r['review_text'],
                'date'     => $r['review_date']
            );
        }
        return $formatted;
    }
}