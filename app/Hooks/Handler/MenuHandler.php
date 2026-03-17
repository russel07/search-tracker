<?php

namespace SearchTracker\Rus\Hooks\Handler;

class MenuHandler
{
    /**
     * Initialize the menu.
     *
     * This method sets up the main menu page and submenu pages.
     */
    public static function init()
    {
        // Add the main menu page.
        add_menu_page(
            __('Search Tracker', 'search-tracker'), // Page Title
            __('Search Tracker', 'search-tracker'), // Menu Title
            'manage_options', // Capability
            SEARCH_TRACKER_ASSET_ID, // Menu Slug
            [new self(), 'loadView'], // Callback
            'dashicons-money', // Dashicon
            54
        );

    }

    /**
     * Load the admin view.
     *
     * This method includes the admin view file.
     */
    public function loadView()
    {
        // Load the admin view.
        include_once( SEARCH_TRACKER_BASE_PATH . '/app/views/admin.php');
    }
}