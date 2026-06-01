<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class FTR_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'check_activation_redirect' ) );
    }

    public function check_activation_redirect() {
        if ( FTR_DB::get_setting( 'do_activation_redirect', false ) ) {
            FTR_DB::delete_setting( 'do_activation_redirect' );
            if ( ! isset( $_GET['activate-multi'] ) ) {
                wp_safe_redirect( admin_url( 'admin.php?page=ftr-reviews' ) );
                exit;
            }
        }
    }

    public function add_admin_menu() {
        add_menu_page( 'Trustpilot Reviews', 'Trustpilot Reviews', 'manage_options', 'ftr-reviews', array( $this, 'render_admin_page' ), 'dashicons-star-filled', 80 );
    }

    private function handle_translation_saves() {
        if ( ! isset( $_POST['ftr_translation_present'] ) ) {
            return;
        }

        $enable_trans = isset($_POST['enable_translation']) ? '1' : '0';
        FTR_DB::update_setting( 'enable_translation', $enable_trans );
        if ( isset($_POST['translate_from']) ) FTR_DB::update_setting( 'translate_from', sanitize_text_field($_POST['translate_from']) );
        if ( isset($_POST['translate_to']) ) FTR_DB::update_setting( 'translate_to', sanitize_text_field($_POST['translate_to']) );
    }

    private function handle_auto_fetch_saves() {
        if ( ! isset( $_POST['ftr_auto_fetch_present'] ) ) {
            return;
        }

        $enabled = isset( $_POST['auto_fetch_enabled'] ) ? '1' : '0';
        FTR_DB::update_setting( 'auto_fetch_enabled', $enabled );

        if ( $enabled !== '1' ) {
            wp_clear_scheduled_hook( 'freetrustpilotreviews_daily_fetch_hook' );
        }
    }

    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $fetch_result = null;

        if ( isset($_POST['ftr_save_translations']) && check_admin_referer('ftr_fetch_action', 'ftr_fetch_nonce') ) {
            if ( isset($_POST['translations']) && is_array($_POST['translations']) ) {
                global $wpdb;
                $table = $wpdb->prefix . 'ftr_reviews';
                
                foreach ( $_POST['translations'] as $tp_id => $tr_data ) {
                    // SAFETY FIX: Check if it's an array (new form) or string (old form)
                    if ( is_array( $tr_data ) ) {
                        $wpdb->update( $table, array( 
                            'review_title_tr' => isset($tr_data['title']) ? sanitize_text_field( wp_unslash( $tr_data['title'] ) ) : '',
                            'review_text_tr'  => isset($tr_data['text']) ? wp_kses_post( wp_unslash( $tr_data['text'] ) ) : ''
                        ), array( 'tp_id' => sanitize_text_field( $tp_id ) ) );
                    } elseif ( is_string( $tr_data ) ) {
                        $wpdb->update( $table, array( 
                            'review_text_tr' => wp_kses_post( wp_unslash( $tr_data ) ) 
                        ), array( 'tp_id' => sanitize_text_field( $tp_id ) ) );
                    }
                }
                FTR_DB::update_setting('cache_version', time()); 
                $fetch_result = array( 'success' => true, 'message' => 'Manual Translations saved & Cache cleared.' );
            }
        }
        
        if ( isset($_POST['ftr_wipe_deactivate']) && check_admin_referer('ftr_wipe_action', 'ftr_wipe_nonce') ) {
            global $wpdb;
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ftr_reviews" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ftr_settings" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ftr_logs" );
            wp_clear_scheduled_hook( 'freetrustpilotreviews_daily_fetch_hook' );
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( FTR_PATH . 'free-trustpilot-reviews.php' ) );
            wp_safe_redirect( admin_url( 'plugins.php?deactivate=true' ) );
            exit;
        }

        if ( isset($_POST['ftr_wipe_and_change_url']) && check_admin_referer('ftr_change_url_action', 'ftr_change_url_nonce') ) {
            wp_clear_scheduled_hook( 'freetrustpilotreviews_daily_fetch_hook' );
            FTR_DB::update_setting( 'target_url', esc_url_raw($_POST['new_target_url']) );
            global $wpdb;
            $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}ftr_reviews" );
            FTR_DB::delete_setting( 'business_name' );
            $fetch_result = array( 'success' => true, 'message' => 'URL updated, crons deleted, and custom tables wiped.' );
        }

        if ( isset($_POST['ftr_manual_fetch']) && check_admin_referer('ftr_fetch_action', 'ftr_fetch_nonce') ) {
            if ( isset($_POST['target_url']) ) FTR_DB::update_setting( 'target_url', esc_url_raw($_POST['target_url']) );
            if ( isset($_POST['sync_hours']) ) FTR_DB::update_setting( 'sync_hours', intval($_POST['sync_hours']) );
            $this->handle_translation_saves();
            $this->handle_auto_fetch_saves();
            
            $fetch_result = FTR_Scraper::fetch();
            FTR_Scraper::schedule_cron();
        }

        if ( isset($_POST['ftr_save_settings']) && check_admin_referer('ftr_fetch_action', 'ftr_fetch_nonce') ) {
            if ( isset($_POST['sync_hours']) ) FTR_DB::update_setting( 'sync_hours', intval($_POST['sync_hours']) );
            if ( isset($_POST['custom_css']) ) FTR_DB::update_setting( 'custom_css', wp_strip_all_tags( wp_unslash( $_POST['custom_css'] ) ) );
            $this->handle_translation_saves();
            $this->handle_auto_fetch_saves();
            
            FTR_DB::update_setting('cache_version', time()); 
            $cron_scheduled = FTR_Scraper::schedule_cron(); 
            $fetch_result = array( 'success' => true, 'message' => $cron_scheduled ? 'Settings saved. Cache cleared and cron rescheduled.' : 'Settings saved. Cache cleared. Automatic fetching is disabled.' );
        }

        // Variable Fetching for Template
        $target_url    = FTR_DB::get_setting( 'target_url', '' );
        $sync_hours    = FTR_DB::get_setting( 'sync_hours', 24 );
        $auto_fetch_enabled = FTR_DB::get_setting( 'auto_fetch_enabled', '1' );
        $custom_css    = FTR_DB::get_setting( 'custom_css', '' );
        $business_name = FTR_DB::get_setting( 'business_name', 'Not Fetched Yet' );
        $reviews       = FTR_DB::get_formatted_reviews(array('limit' => 200));
        $logs          = FTR_DB::get_logs(100); 
        
        $enable_translation = FTR_DB::get_setting( 'enable_translation', '0' );
        $translate_from     = FTR_DB::get_setting( 'translate_from', 'auto' );
        $translate_to       = FTR_DB::get_setting( 'translate_to', 'en' );
        
        $status_last_fetch   = FTR_DB::get_setting('last_fetch_time', 'Never');
        $status_last_success = FTR_DB::get_setting('last_success_time', 'Never');
        $status_last_error   = FTR_DB::get_setting('last_error_message', 'None');
        $status_inserted     = FTR_DB::get_setting('total_inserted', '0');

        $next_run = wp_next_scheduled( 'freetrustpilotreviews_daily_fetch_hook' );
        $time_diff = $auto_fetch_enabled === '1' ? 'Not scheduled' : 'Auto-fetch disabled';
        if ( $auto_fetch_enabled === '1' && $next_run ) {
            $diff_seconds = $next_run - time();
            $time_diff = $diff_seconds <= 0 ? 'Pending' : floor($diff_seconds / 3600) . 'h ' . floor(($diff_seconds % 3600) / 60) . 'm';
        }

        $is_wizard = empty( $reviews ) && empty( $target_url );

        require_once FTR_PATH . 'templates/admin-page.php';
    }
}