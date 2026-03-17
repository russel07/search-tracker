<?php

namespace SearchTracker\Rus\Config;
use SearchTracker\Rus\Foundation\Application;
use SearchTracker\Rus\Hooks\Handler\ActivationHandler;

/**
 * Class Bootstrap
 *
 * Bootstraps the WPSS Social Share plugin.
 */
class Bootstrap
{
    /**
     * Bootstraps the plugin.
     */
    public function boot()
    {
        // Include REST API routes.
        add_action('rest_api_init', function () {
            require_once SEARCH_TRACKER_BASE_PATH . 'app/Http/Router/Api.php';
        });
        $app = Application::instance();
        $app->load();
    }

    /**
     * Handles plugin activation.
     */
    public static function activate_me()
    {
        // Handle plugin activation.
        (new ActivationHandler())->handle();
    }

    /**
     * Handles plugin deactivation.
     */
    public static function deactivate_me()
    {
        // Remove custom user roles on plugin deactivation.
        remove_role('wplms_cleanup_pro_manager');
    }
}