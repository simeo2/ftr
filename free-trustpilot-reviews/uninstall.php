<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

// Drop ALL Custom Tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ftr_reviews" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ftr_settings" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ftr_logs" );

// Clear Cron
wp_clear_scheduled_hook( 'freetrustpilotreviews_daily_fetch_hook' );