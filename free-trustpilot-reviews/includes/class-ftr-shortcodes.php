<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class FTR_Shortcodes {

    public function __construct() {
        add_shortcode( 'ftr_slider', array( $this, 'render_slider' ) );
        add_shortcode( 'ftr_all', array( $this, 'render_grid' ) );
        add_shortcode( 'ftr', array( $this, 'render_specific' ) ); 
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
    }

    public function register_assets() {
        $version = defined( 'FTR_VERSION' ) ? FTR_VERSION : '1.5.0';
        wp_register_style( 'ftr-style', FTR_URL . 'assets/css/style.css', array(), $version );
        wp_register_script( 'ftr-slider-js', FTR_URL . 'assets/js/slider.js', array(), $version, true );
    }

    private function load_assets( $needs_js = false ) {
        wp_enqueue_style( 'ftr-style' );
        if ( $needs_js ) wp_enqueue_script( 'ftr-slider-js' );
        $custom_css = FTR_DB::get_setting( 'custom_css', '' );
        if ( !empty($custom_css) ) wp_add_inline_style( 'ftr-style', wp_strip_all_tags( $custom_css ) );
    }

    // Helper: Build Cache Key
    private function get_cache_key( $type, $atts ) {
        $cache_version = FTR_DB::get_setting( 'cache_version', '1' );
        return 'ftr_sc_' . md5( serialize($atts) . $type . $cache_version );
    }

    // Helper: Parse Atts into SQL Args
    private function parse_db_args( $atts, $default_limit = 0 ) {
        $args = array( 'limit' => $atts['limit'] !== '' ? intval($atts['limit']) : $default_limit );
        if ( !empty($atts['id']) ) $args['ids'] = explode( ',', $atts['id'] );
        if ( !empty($atts['no-id']) ) $args['exclude_ids'] = explode( ',', $atts['no-id'] );
        return $args;
    }

    public function render_slider( $atts ) {
        $atts = shortcode_atts( array( 'no-id' => '', 'limit' => 10 ), $atts );
        $cache_key = $this->get_cache_key( 'slider', $atts );
        
        $cached_output = get_transient( $cache_key );
        if ( $cached_output !== false ) {
            $this->load_assets( true );
            return $cached_output;
        }

        $this->load_assets( true );
        $latest_10 = FTR_DB::get_formatted_reviews( $this->parse_db_args($atts, 10) );
        $target_url = add_query_arg( array( 'utm_medium' => 'trustbox', 'utm_source' => 'TrustBoxReviewCollector' ), FTR_DB::get_setting( 'target_url', '' ) );

        ob_start();
        require FTR_PATH . 'templates/shortcode-slider.php';
        $output = ob_get_clean();
        
        set_transient( $cache_key, $output, 24 * HOUR_IN_SECONDS );
        return $output;
    }

    public function render_grid( $atts ) {
        $atts = shortcode_atts( array( 'no-id' => '', 'limit' => '' ), $atts ); // No limit by default for [ftr_all] unless specified
        $cache_key = $this->get_cache_key( 'all', $atts );

        $cached_output = get_transient( $cache_key );
        if ( $cached_output !== false ) {
            $this->load_assets( false );
            return $cached_output;
        }

        $this->load_assets( false );
        $reviews = FTR_DB::get_formatted_reviews( $this->parse_db_args($atts, 0) );

        ob_start();
        require FTR_PATH . 'templates/shortcode-grid.php';
        $output = ob_get_clean();
        
        set_transient( $cache_key, $output, 24 * HOUR_IN_SECONDS );
        return $output;
    }

    public function render_specific( $atts ) {
        $atts = shortcode_atts( array( 'id' => '', 'no-id' => '', 'limit' => '' ), $atts );
        if ( empty($atts['id']) && empty($atts['no-id']) ) return '';
        
        $cache_key = $this->get_cache_key( 'specific', $atts );
        $cached_output = get_transient( $cache_key );
        if ( $cached_output !== false ) {
            $this->load_assets( false );
            return $cached_output;
        }

        $this->load_assets( false );
        $reviews = FTR_DB::get_formatted_reviews( $this->parse_db_args($atts, 0) );

        ob_start();
        require FTR_PATH . 'templates/shortcode-grid.php';
        $output = ob_get_clean();

        set_transient( $cache_key, $output, 24 * HOUR_IN_SECONDS );
        return $output;
    }
}