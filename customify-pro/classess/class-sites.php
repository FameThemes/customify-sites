<?php

Class Customify_Sites {
    static $_instance = null;
    const THEME_NAME = 'customify';


    function admin_scripts( $id ){
        if( $id == 'appearance_page_customify-sites' ){
            wp_localize_script('jquery', 'Customify_Sites',  $this->get_localize_script() );
            wp_enqueue_style('owl.carousel', CUSTOMIFY_SITES_URL.'/assets/css/owl.carousel.css' );
            wp_enqueue_style('owl.theme.default', CUSTOMIFY_SITES_URL.'/assets/css/owl.theme.default.css' );
            wp_enqueue_style('customify-sites', CUSTOMIFY_SITES_URL.'/assets/css/customify-sites.css' );

            wp_enqueue_script('owl.carousel', CUSTOMIFY_SITES_URL.'/assets/js/owl.carousel.min.js',  array( 'jquery' ), false, true );
            wp_enqueue_script('customify-sites', CUSTOMIFY_SITES_URL.'/assets/js/backend.js',  array( 'jquery', 'underscore' ), false, true );
        }
    }

    static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
            add_action( 'admin_menu', array( self::$_instance, 'add_menu' ), 50 );
            add_action( 'admin_enqueue_scripts', array( self::$_instance, 'admin_scripts' ) );
            add_action( 'admin_notices', array( self::$_instance, 'admin_notice' ) );
            new Customify_Sites_Ajax();
        }
        return self::$_instance;
    }

    function admin_notice( $hook ) {
        $screen = get_current_screen();
        if( $screen->id != 'appearance_page_customify-sites' && $screen->id != 'themes' ) {
            return '';
        }

        if( get_template() == self::THEME_NAME  ) {
            return '';
        }

        $themes = wp_get_themes();
        if ( isset( $themes[ self::THEME_NAME ] ) ) {
            $url = esc_url( 'themes.php?theme='.self::THEME_NAME );
        } else {
            $url = esc_url( 'theme-install.php?search='.self::THEME_NAME );
        }

        $html = sprintf( 'Customify Theme needs to be active for you to use currently installed "Customify Sites" plugin. <a href="%1$s">Install &amp; Activate Now</a>', $url );
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php echo $html; ?>
            </p>
        </div>
        <?php
    }

    static function get_api_url(){
        return apply_filters( 'customify_sites/api_url', 'https://customifysites.com/wp-json/wp/v2/sites/' );
    }

    function add_menu() {
        add_theme_page(__( 'Customify Sites', 'customify-sites' ), __( 'Customify Sites', 'customify-sites' ), 'edit_theme_options', 'customify-sites', array( $this, 'page' ));
    }

    function page(){
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">'.__( 'Customify Sites', 'customify-sites' ).'</h1>';
        require_once CUSTOMIFY_SITES_PATH.'/templates/dashboard.php';
        require_once CUSTOMIFY_SITES_PATH.'/templates/modal.php';
        echo '</div>';
    }

    function get_installed_plugins(){
        // Check if get_plugins() function exists. This is required on the front end of the
        // site, since it is in a file that is normally only loaded in the admin.
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();
        if ( ! is_array( $all_plugins ) ) {
            $all_plugins = array();
        }

        $plugins = array();
        foreach ( $all_plugins as $file => $info ) {
            $slug = dirname( $file );
            $plugins[ $slug ] = $info['Name'];
        }

        return $plugins;
    }

    function get_activated_plugins(){
        $activated_plugins = array();
        foreach( ( array ) get_option('active_plugins') as $plugin_file ) {
            $plugin_file = dirname( $plugin_file );
            $activated_plugins[ $plugin_file ] = $plugin_file;
        }
        return $activated_plugins;
    }

    function get_support_plugins(){
        $plugins = array(
            'customify-pro' => _x( 'Customify Pro', 'plugin-name', 'customify-sites' ),

            'elementor' => _x( 'Elementor', 'plugin-name', 'customify-sites' ),
            'elementor-pro' => _x( 'Elementor Pro', 'plugin-name', 'customify-sites' ),
            'beaver-builder-lite-version' => _x( 'Beaver Builder', 'plugin-name', 'customify-sites' ),
            'contact-form-7' => _x( 'Contact Form 7', 'plugin-name', 'customify-sites' ),

            'breadcrumb-navxt' => _x( 'Breadcrumb NavXT', 'plugin-name', 'customify-sites' ),
            'jetpack' => _x( 'JetPack', 'plugin-name', 'customify-sites' ),
            'easymega' => _x( 'Mega menu', 'plugin-name', 'customify-sites' ),
            'polylang' => _x( 'Polylang', 'plugin-name', 'customify-sites' ),
            'woocommerce' => _x( 'WooCommerce', 'plugin-name', 'customify-sites' ),
        );

        return $plugins;
    }

    function get_localize_script(){
        $args = array(
            'api_url' => self::get_api_url(),
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'is_admin' => is_admin(),
            'try_again' => __( 'Try Again', 'customify-site', 'customify-sites' ),
            'activated_plugins' => $this->get_activated_plugins(),
            'installed_plugins' => $this->get_installed_plugins(),
            'support_plugins' => $this->get_support_plugins(),
        );
        return $args;
    }

}