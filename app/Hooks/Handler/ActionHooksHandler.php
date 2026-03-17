<?php

namespace SearchTracker\Rus\Hooks\Handler;

use SearchTracker\Rus\Http\Model\GiftCode;
use SearchTracker\Rus\Http\Model\UserBalance;
use SearchTracker\Rus\Foundation\AppHelper;
use SearchTracker\Rus\Services\Mailer;

class ActionHooksHandler
{
    use AppHelper;

    public static function get_search_terms_and_keywords()
    {
        if ( is_search() ) {
            global $wpdb;

            $search_term = get_search_query();

            if (!empty($search_term)) {
                $wpdb->insert(
                    $wpdb->prefix . 'search_tracker_logs',
                    [
                        'search_term' => sanitize_text_field($search_term),
                        'user_id'     => get_current_user_id(),
                        'ip_address'  => $_SERVER['REMOTE_ADDR'],
                        'created_at'  => current_time('mysql')
                    ]
                );
            }
        }
    }
}
