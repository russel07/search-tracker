<?php

/**
 * Plugin Name:       Search Tracker
 * Description:       A simple plugin to track search queries.
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Md. Russel Hussain
 * Author URI:        https://russelhussain.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       search-tracker
 */

defined('SEARCH_TRACKER_ASSET_ID') || define('SEARCH_TRACKER_ASSET_ID', 'search-tracker');
defined('SEARCH_TRACKER_ASSET_VARS') || define('SEARCH_TRACKER_ASSET_VARS', 'wplms_cleanup_pro');

// Define the plugin constant PLUGIN_VERSION if not already defined.
defined('SEARCH_TRACKER_VERSION') || define('SEARCH_TRACKER_VERSION', '1.0.3');

// Define the plugin constant SEARCH_TRACKER_TEXT_DOMAIN if not already defined.
defined('SEARCH_TRACKER_TEXT_DOMAIN') || define('SEARCH_TRACKER_TEXT_DOMAIN', 'search-tracker');

// Define the plugin constant SEARCH_TRACKER_DIR if not already defined.
defined('SEARCH_TRACKER_DIR') || define('SEARCH_TRACKER_DIR', __DIR__);

// Define the plugin constant SEARCH_TRACKER_URL if not already defined.
defined('SEARCH_TRACKER_URL') || define('SEARCH_TRACKER_URL',  plugin_dir_url(__FILE__));

// Define the plugin constant SEARCH_TRACKER_BASE_PATH if not already defined.
defined('SEARCH_TRACKER_BASE_PATH') || define('SEARCH_TRACKER_BASE_PATH',  plugin_dir_path(__FILE__));

// Check for the autoloader file's existence and include it.
if ( file_exists(__DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    wp_die( esc_html__( 'Autoload file is missing.', 'search-tracker' ) );
}


// Register activation hook with a callable array method.
register_activation_hook( __FILE__, array('SearchTracker\Rus\Config\Bootstrap', 'activate_me' ) );

// Register deactivation hook to remove custom user roles.
register_deactivation_hook(__FILE__, array('SearchTracker\Rus\Config\Bootstrap', 'deactivate_me' ) );

/**
 * Initializes the Search Tracker plugin.
 */
function rus_search_tracker_init()
{
    if (class_exists('SearchTracker\Rus\Config\Bootstrap')) {
        $bootstrap = new SearchTracker\Rus\Config\Bootstrap();
        $bootstrap->boot();
    } else {
        wp_die( esc_html__( 'Search Tracker is not properly installed.', 'search-tracker' ) );
    }
}

add_action( 'plugins_loaded', 'rus_search_tracker_init' );