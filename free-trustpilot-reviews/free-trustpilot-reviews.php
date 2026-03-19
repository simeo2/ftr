<?php
/**
 * Plugin Name: Free Trustpilot Reviews for WP
 * Plugin URI: https://github.com/simeo2/ftr
 * Description: A robust, production-ready plugin to fetch, cache, and display Trustpilot reviews utilizing isolated custom tables, sync locks, and transient caching.
 * Version: 1.5.1
 * Author: Simeon Zahariev
 * Text Domain: free-tp-reviews
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FTR_PATH', plugin_dir_path( __FILE__ ) );
define( 'FTR_URL', plugin_dir_url( __FILE__ ) );
define( 'FTR_VERSION', '1.5.1' );

require_once FTR_PATH . 'includes/class-ftr-db.php';
require_once FTR_PATH . 'includes/class-ftr-scraper.php';
require_once FTR_PATH . 'includes/class-ftr-admin.php';
require_once FTR_PATH . 'includes/class-ftr-shortcodes.php';

function ftr_init() {
    FTR_Scraper::init(); 
    new FTR_Shortcodes();
    if ( is_admin() ) {
        new FTR_Admin();
    }
}
add_action( 'plugins_loaded', 'ftr_init' );

register_activation_hook( __FILE__, array( 'FTR_DB', 'activate' ) );
register_deactivation_hook( __FILE__, 'ftr_deactivate_plugin' );

function ftr_deactivate_plugin() {
    wp_clear_scheduled_hook( 'freetrustpilotreviews_daily_fetch_hook' );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ftr_action_links' );
function ftr_action_links( $links ) {
    $settings_link = '<a href="admin.php?page=ftr-reviews">Settings</a>';
    $docs_link     = '<a href="admin.php?page=ftr-reviews#help">Documentation</a>';
    array_unshift( $links, $docs_link );
    array_unshift( $links, $settings_link );
    return $links;
}