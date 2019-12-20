=== Clear Cache for Me ===
Contributors: webheadllc
Donate link: https://webheadcoder.com/donate-clear-cache-for-me
Tags: wpengine, widget, menu, cache, clear, purge, w3t, W3 Total Cache, WP Super Cache, WP Fastest Cache, refresh, update, empty, performance, optimization, contact form 7, woothemes, ngg, gallery, Qode, theme, cache buster
Requires at least: 3.8
Tested up to: 5.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Purges all cache on WPEngine, W3 Total Cache, WP Super Cache, WP Fastest Cache when updating widgets, menus, settings.  Forces a browser to reload a theme's CSS and JS files.

== Description ==

W3 Total Cache and WP Super Cache are great caching plugins, but they do not know when a widget is updated.  WPEngine is the best place to host your WordPress installation, but their caching system is no smarter when it comes to updating widgets and menus.  I created this plugin because my website did not see any changes when saving widgets or menus using these caching systems.  Clear Cache For Me will purge ALL your cache each time you do a save without having to press an additional button.  It may be overkill, which may be why it's not built in, but some people need simplicity.

In addition to clearing those pesky caching engines, Clear Cache for Me can force your browser to reload your current theme's CSS and JS files.  I modify my theme's CSS and JS files every so often and always have trouble with the browser not getting the latest version.  So now after clicking on the "Clear Cache Now!" button on the dashboard the browser will be forced to reload the current theme's CSS and JS files.  If you do not click the "Clear Cache Now!" button, the browser will cache the CSS and JS files like it normally does.

The popular Qode themes has a options to set your own custom CSS and JS.  Sometimes you may not see your changes for a long while because your browser is trying to get the cached file.  Whenever you save your Qode's options, the CSS and JS files will be forced to reload in the browser on the public side.

Works with the following caching systems:

* W3 Total Cache
* WP Super Cache
* WP Fastest Cache
* WPEngine hosting

Clears all cache for following actions:

* When Widgets are saved
* When Customizer is saved
* When Menus are saved
* When Settings from a settings page is saved.
* When a Contact Form 7 form is saved.
* When WooThemes settings are saved.
* When NextGen Gallery albums and galleries are updated (beta - may not clear cache on all actions).
* When Qode options are saved this plugin forces browsers to reload the custom css and custom js.

There is a convenient clear cache button on the dashboard for users with the right capability.  Admins (users with the 'manage_options' capability) can set which capability a user needs to view the button.  If you are using this button often, please consider submitting a request to have this plugin do your button-pushing for you.  This plugin is meant to work behind the scenes to make your life easier and less frustrating.

== Screenshots ==

1. The mythical button.  

== Changelog ==

= 1.0 =
Added clearing cache for all JS and CSS theme files.  
Added clearing cache when Qode theme options are saved.  

= 0.93 =
Fixed button not showing up when admin doesn't have permissions.  Button will now always show for the admin user with manage_options capability.  

= 0.92 =
Fixed clearing cache on widgets when widgets are saved or reordered.  

= 0.91 =
Minor fix checking if certain WPEngine functions exist.  Thanks to @tharmann!  

= 0.9 =
Added clear cache for NextGen Gallery saving, but not sure if all actions are accounted for.

= 0.8 =
Added clear cache for WooThemes options.  
Fixed cache not clearing on some WP Super Cache installations.

= 0.7.1 =
Added clear cache for settings pages.  
Added clear cache for Contact Form 7 form saving.  
Updated description and added donation link on plugin page only.

= 0.6.2 =
minor updates to css class names

= 0.6.1 =
Updated German translation (thanks to Ov3rfly!).  
Updated admin HTML and styles (thanks to Ov3rfly!).

= 0.6 =
Fixed cache not clearing when widgets are re-ordered or deleted (thanks to Ov3rfly!).  
Added optional instructions to be shown above the button (thanks to Ov3rfly!).  
Added to and updated German translations (thanks to Ov3rfly!).  
Added more security checks. (thanks to Ov3rfly!).  
Added customize theme detection.  Clears cache when customizing theme.  
Reorganized code a bit.

= 0.5 =
Added German language translation (thanks to Ov3rfly)  
Added hooks for 3rd party code.

= 0.4 =
Bug fixed: Fixed cache not clearing when updating nav menu. (thanks to Ov3rfly for catching this and supplying the fix)

= 0.3 =
Added clear caching for menus  
Added clear cache button to dashboard  
Added option to set what capability is needed to view the clear cache button for admins.  

= 0.2 =
Removed garbage at the end of the plugin url.

= 0.1 =
Initial release.


== Developer Options ==

= ccfm_supported_caching_exists =  
Use this filter to determine if this plugin should do anything including showing the button on the dashboard.  Return true if a caching system is supported.  
Default: True if any of the supported caching systems is active.  
See Example 1 below.

= ccfm_admin_init =  
Use this action to add hooks when cache is to be cleared.  Or do any other setup activity.  

= ccfm_clear_cache_for_me_before = 
Use this action to clear cache from an unsupported caching system before the default caching systems clear their cache.

= ccfm_clear_cache_for_me = 
Use this action to clear cache from an unsupported caching system after the default caching systems clear their cache.


= Example 1 = 
Thanks to Benjamin Pick - If you are using Autoptimize and need to clear the CSS or JS files automatically, you can add the code below to your theme's functions.php file.  This code also demonstrates how you can add an unsupported caching system and have its cache cleared for you.  
https://gist.github.com/benjaminpick/94b487ce995454797143  
also pasted below:  
`<?php
function yt_cache_enable($return) {
    if (class_exists('autoptimizeCache'))
        return true;
    
    return $return;
}
add_filter('ccfm_supported_caching_exists', 'yt_cache_enable');

function yt_cache_clear() {
    if (class_exists('autoptimizeCache'))
        autoptimizeCache::clearall();
}
add_action('ccfm_clear_cache_for_me', 'yt_cache_clear');`


= Example 2 =
Thanks to Benjamin Pick - If you have an automatic deployment setup and need a webhook to clear the caches, you can add this code to your theme's functions.php file.  
https://gist.github.com/benjaminpick/67b6b9a49ef7991172f9  
also pasted below:  
`<?php
if (!defined('CLEAR_CACHE_HOOK_KEY'))
    define('CLEAR_CACHE_HOOK_KEY', 'some_secret_key_please');

function yt_cache_clear_web_hook() {
    if (isset($_GET['key']) && $_GET['key'] == CLEAR_CACHE_HOOK_KEY) {
        if (function_exists('ccfm_clear_cache_for_me')) {
            ccfm_clear_cache_for_me( 'ajax' );
            echo 'Cache was cleared.';
        } else {
            echo 'Install the plugin "Clear Cache For Me" first';
        }
        exit;
    }
}

// Call this URL to clear the cache:
// /wp-admin/admin-ajax.php?action=clear_cache&key=some_secret_key_please

add_action( 'wp_ajax_clear_cache', 'yt_cache_clear_web_hook' );
add_action( 'wp_ajax_nopriv_clear_cache', 'yt_cache_clear_web_hook' );
`
