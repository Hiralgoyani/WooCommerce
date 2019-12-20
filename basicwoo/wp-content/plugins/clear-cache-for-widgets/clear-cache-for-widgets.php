<?php 
/*
Plugin Name: Clear Cache For Me
Plugin URI: https://webheadcoder.com/clear-cache-for-me/
Description: Purges all cache on WPEngine, W3 Total Cache, WP Super Cache, WP Fastest Cache when updating widgets, menus, settings.  Forces a browser to reload a theme's CSS and JS files.
Author: Webhead LLC
Author URI: https://webheadcoder.com 
Version: 1.0
*/


require_once( 'clear-cache-for-action.php' );

// locale
function ccfm_plugins_loaded() {
    load_plugin_textdomain( 'ccfm', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'ccfm_plugins_loaded' );


/**
 * Add widget save, reorder and delete detection.  Thanks to Ov3rfly.
 */
function ccfm_admin_init() {
    global $pagenow;

    ccfm_handle_requests();
    ccfm_set_capability();

    add_action( 'wp_dashboard_setup', 'ccfm_dashboard_widget' );
    
    //add styles for dashboard
    add_action( 'admin_head', 'ccfm_admin_head' );

    if ( defined( 'QODE_ROOT' ) ) {
        if ( 'options.php' == $pagenow ) {
            //detect when qode options saved.
            add_action( 'updated_option', 'ccfm_save_clear_cache_for_qode', 10, 3 );   
        }
    }

    if ( ccfm_supported_caching_exists() ) {

        //detect widget save, reorder and delete detection.  Thanks to Ov3rfly.
        add_action( 'wp_ajax_save-widget', 'ccfm_clear_cache_for_widgets_wp_ajax_action', 1 );
        add_action( 'wp_ajax_widgets-order', 'ccfm_clear_cache_for_widgets_wp_ajax_action', 1 );
        add_action( 'sidebar_admin_setup', 'ccfm_clear_cache_for_widgets_sidebar_admin_setup' );
        //detect customize theme actions.
        add_action( 'customize_save_after', 'ccfm_clear_cache_for_customized_theme' );

        //detect nav menu changes
        add_action( 'wp_update_nav_menu', 'ccfm_clear_cache_for_menus' );

        //detect settings page changes
        add_filter( 'pre_set_transient_settings_errors', 'ccfm_clear_cache_for_settings' );

        //detect ContactForm7 changes
        add_action( 'wpcf7_save_contact_form', 'ccfm_clear_cache_for_cf7' );

        //detect WooThemes settings changes
        add_action( 'update_option_woo_options', 'ccfm_clear_cache_for_woo_options' );

        //try detect NextGen Gallery changes
        add_action( 'ngg_update_gallery', 'ccfm_clear_cache_for_ngg' );
        add_action( 'ngg_delete_gallery', 'ccfm_clear_cache_for_ngg' );
        add_action( 'ngg_update_album', 'ccfm_clear_cache_for_ngg' );
        add_action( 'ngg_update_album_sortorder', 'ccfm_clear_cache_for_ngg' );
        add_action( 'ngg_delete_album', 'ccfm_clear_cache_for_ngg' );

        do_action( 'ccfm_admin_init' );
    }
}
add_action( 'admin_init', 'ccfm_admin_init' ); // not 'init'

/**
 * Return true if known caching systems exists.
 */
function ccfm_supported_caching_exists() {
    $supported = function_exists( 'w3tc_pgcache_flush' ) 
               || function_exists( 'wp_cache_clean_cache' )
               || class_exists( 'WpeCommon' )
               || method_exists( 'WpFastestCache', 'deleteCache' );
    return apply_filters( 'ccfm_supported_caching_exists', $supported );
}

/**
 * Clear the caches!
 */
function ccfm_clear_cache_for_me( $source ) {
    global $wp_fastest_cache;

    do_action( 'ccfm_clear_cache_for_me_before', $source );

    // if W3 Total Cache is being used, clear the cache
    if ( function_exists( 'w3tc_pgcache_flush' ) ) { 
        w3tc_pgcache_flush(); 
    }
    // if WP Super Cache is being used, clear the cache
    else if ( function_exists( 'wp_cache_clean_cache' ) ) {
        global $file_prefix, $supercachedir;
        if ( empty( $supercachedir ) && function_exists( 'get_supercache_dir' ) ) {
            $supercachedir = get_supercache_dir();
        }
        wp_cache_clean_cache( $file_prefix );
    }
    else if ( class_exists( 'WpeCommon' ) ) {
        //be extra careful, just in case 3rd party changes things on us
        if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
            WpeCommon::purge_memcached();
        }
        if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) ) {  
            WpeCommon::clear_maxcdn_cache();
        }
        if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
            WpeCommon::purge_varnish_cache();   
        }
    }
    else if ( method_exists( 'WpFastestCache', 'deleteCache' ) && !empty( $wp_fastest_cache ) ) {
        $wp_fastest_cache->deleteCache();
    }
    do_action( 'ccfm_clear_cache_for_me', $source );

    $time = time();
    update_option( '_ccfm_style_timestamp_theme', $time );

    do_action( 'ccfm_clear_cache_for_css_js', $time );
}

/**
 * Add a button to clear the cache on the dashboard.
 */
function ccfm_dashboard_widget() {
    $needed_cap = get_option( 'ccfm_permission', 'manage_options' );
    if ( current_user_can( $needed_cap ) || current_user_can( 'manage_options' ) ) {
        wp_add_dashboard_widget('dashboard_ccfm_widget', 'Clear Cache for Me', 'ccfm_dashboard_widget_output');       
    }
}

function ccfm_dashboard_widget_output() {
    $needed_cap = get_option( 'ccfm_permission', 'manage_options' );
    $infotext = get_option( 'ccfm_infotext', '' );
    ?>
<div class="ccfm_widget">
    <form method="post">
    <?php echo ( $infotext ) ? '<p>' . $infotext . '</p>' : ''; ?>
    <p>
        <?php wp_nonce_field( 'ccfm' ); ?>
        <input type="submit" name="ccfm" class="button button-primary button-large" value="<?php _e( 'Clear Cache Now!', 'ccfm' ); ?>">
    </p>
    </form>
</div>
    <?php
        if ( current_user_can( 'manage_options' ) ) {
            global $wp_roles;
            $roles = $wp_roles->roles;
            $caps = array();
            foreach( $roles as $role ) {
                if ( !empty( $role['capabilities'] ) ) {
                    foreach ( $role['capabilities'] as $capability => $val ) {
                        $caps[ $capability ] = $capability;
                    }   
                }
            }
            asort( $caps );
    ?>
<div class="ccfm_widget">
    <h4><?php _e( 'Settings', 'ccfm' ); ?></h4>
    <form method="post">
    <label for="ccfm_permission"><?php _e( 'Show button for users with capability:', 'ccfm' ); ?></label>
    <p>
        <select name="ccfm_permission" id="ccfm_permission">
            <?php foreach ( $caps as $cap ) : ?>
                <option value="<?php echo esc_attr($cap); ?>" <?php selected( $needed_cap, $cap );?>><?php echo $cap; ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <label for="ccfm_infotext"><?php _e( 'Instructions to show above button (optional):', 'ccfm' ); ?></label>
    <p>
        <input id="ccfm_infotext" name="ccfm_infotext" type="text" value="<?php echo esc_attr( $infotext); ?>" />
    </p>
    <p>
        <?php wp_nonce_field( 'ccfm' ); ?>
        <input type="hidden" name="ccfm_set" value="1" />
        <input type="submit" class="button button-large" value="<?php _e( 'Set', 'ccfm' ); ?>">
    </p>
    </form>
</div>
    <?php
    }
}

/**
 * Add some CSS.
 */
function ccfm_admin_head() {
?>
<style type="text/css">
#dashboard_ccfm_widget .inside {
    margin: 0;
    padding: 0;
}
#dashboard_ccfm_widget .ccfm_widget {
    border-top: 1px solid #eee;
    font-size: 13px;
    padding: 8px 12px 4px 12px;
}
#dashboard_ccfm_widget .ccfm_widget:first-child {
    border-top: none;
}
#dashboard_ccfm_widget h4 {
    margin-bottom: 4px;
}
#dashboard_ccfm_widget p {
    margin: 0 0 8px 0;
}
#dashboard_ccfm_widget label {
    display: block;
    margin: 0 0 4px 0;
    color: #777;
}
#dashboard_ccfm_widget input[name="ccfm_infotext"] {
    width: 80%;
}
</style>
<?php
}


/**
 * Clear the cache if requested.
 */
function ccfm_handle_requests() {
    if ( isset( $_POST['ccfm'] ) ) {
        check_admin_referer( 'ccfm' );
        $needed_cap = get_option( 'ccfm_permission', 'manage_options' );
        $is_success = 0;
        if ( current_user_can( $needed_cap ) ) {
            ccfm_clear_cache_for_me( 'button' );
            $is_success = 1;
            add_action( 'admin_notices', 'ccfm_success' );
        }
        else {
            add_action( 'admin_notices', 'ccfm_error' );   
        }
        wp_safe_redirect( admin_url() . '?ccfm_success=' . $is_success );
        exit;
    }

    if ( isset( $_GET['ccfm_success'] ) ) {
        if ( !empty( $_GET['ccfm_success'] ) ) {
            add_action( 'admin_notices', 'ccfm_success' );
        }
        else {
            add_action( 'admin_notices', 'ccfm_error' );   
        }
    }
    if ( isset( $_GET['ccfm_asuccess'] ) ) {
        if ( !empty( $_GET['ccfm_asuccess'] ) ) {
            add_action( 'admin_notices', 'ccfm_admin_success' );
        }
        else {
            add_action( 'admin_notices', 'ccfm_error' );   
        }
    }
}

/**
 * Set the capability needed to view the button.
 */
function ccfm_set_capability() {
    if ( isset( $_POST['ccfm_set'] ) ) {
        check_admin_referer( 'ccfm' );
        $is_success = 0;
        if ( current_user_can( 'manage_options' ) ) {
            update_option( 'ccfm_permission', sanitize_title( $_POST['ccfm_permission'] ) );
            update_option( 'ccfm_infotext', wp_unslash( $_POST['ccfm_infotext'] ) );
            $is_success = 1;
        }
        wp_safe_redirect( admin_url() . '?ccfm_asuccess=' . $is_success  );
        exit;
    }
}

/**
 * Show the success notice.
 */
function ccfm_success() { ?>
    <div class="updated">
        <p><?php _e( 'Cache cleared!', 'ccfm' ); ?></p>
    </div>
<?php
}

/**
 * Show the success notice for saving options.
 */
function ccfm_admin_success() { ?>
    <div class="updated">
        <p><?php _e( 'Settings Saved!', 'ccfm' ); ?></p>
    </div>
<?php
}

/**
 * Show the error notice.
 */
function ccfm_error() { ?>
    <div class="error">
        <p><?php _e( 'You do not have permission to do that.', 'ccfm' ); ?></p>
    </div>
<?php
}



/**
 * If we have urls to uncache, setup filters.
 * - must be at wp_head priority 1 for style_loader_src to work.
 */
function ccfm_clear_cache_for_custom_styles() {
    add_filter( 'style_loader_src', 'ccfm_show_src_version', 10, 2 );
    add_filter( 'script_loader_src', 'ccfm_show_src_version', 10, 2 );
}
add_action( 'wp_head', 'ccfm_clear_cache_for_custom_styles', 1 );

/**
 * Change a src to a key.
 */
function ccfm_custom_src_key( $src ) {
    $urls = apply_filters( 'ccfm_custom_src_urls', array() );
    $src_parts = explode( '?', $src );
    foreach( $urls as $url => $key ) {
        if ( $src_parts[0] === $url ) {
            return $key;
        }   
    }
    $template = get_template_directory_uri();
    $child = get_stylesheet_directory_uri();
    if ( stripos( $src, $template ) === 0 || stripos( $src, $child ) === 0 ) {
        return 'theme';
    }

    return '';
}

/**
 * Filter the version to make sure it's the latest.
 */    
function ccfm_show_src_version ( $src, $handle ) {
    $key = ccfm_custom_src_key( $src );
    $timestamp = get_option( '_ccfm_style_timestamp_' . $key, '' );
    if ( empty( $timestamp ) ) {
        return $src;
    }
    $src_parts = explode( '?', $src );
    if ( count( $src_parts ) > 1 ) {
        $query_string = $src_parts[1];
        parse_str( $query_string, $query );
        $query['ver'] = $timestamp;
        return add_query_arg( $query, $src_parts[0] );
    }
    return add_query_arg( 'ver', $timestamp, $src );
}