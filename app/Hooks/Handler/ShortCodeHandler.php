<?php

namespace SearchTracker\Rus\Hooks\Handler;

use SearchTracker\Rus\Foundation\AppHelper;
use SearchTracker\Rus\Http\Model\UserBalance;
use SearchTracker\Rus\Http\Model\GiftCode;

class ShortCodeHandler
{
    use AppHelper;
    
    public function __construct()
    {
        add_shortcode('your_short_code_name', [$this, 'render_your_short_code_name']);
    }

    public function render_your_short_code_name()
    {
        $this->load_your_short_code_name_assets();
        ob_start(); // Start output buffering
        echo '<div id="your_short_code_name_app"></div>';
        return ob_get_clean(); // Return the buffered content
    }

    public function load_your_short_code_name_assets()
    {
        // Enqueue user and shortcode area
        wp_enqueue_style(SEARCH_TRACKER_ASSET_ID . '-style', SEARCH_TRACKER_URL . 'assets/css/gift_card_customizer.css');

        // Enqueue JavaScript for  user and shortcode area
        wp_enqueue_script(SEARCH_TRACKER_ASSET_ID, SEARCH_TRACKER_URL . 'assets/js/customizer.js', array('jquery', 'moment'), '1.0.0', true);
        wp_localize_script(SEARCH_TRACKER_ASSET_ID, SEARCH_TRACKER_ASSET_VARS . '_app_vars', $this->e_gift_card_app_vars());
    }
}
