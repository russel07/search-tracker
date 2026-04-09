<?php

namespace SearchTracker\Rus\Hooks\Handler;

use SearchTracker\Rus\Foundation\AppHelper;
use SearchTracker\Rus\Http\Model\FilterMeta;

class ActionHooksHandler
{
    use AppHelper;

    const META_FILTER_IP = 'FILTER_IP';
    const META_FILTER_WORD = 'FILTER_WORD';

    protected static $filterCache = null;

    public static function get_search_terms_and_keywords()
    {
        if ( is_search() ) {
            global $wpdb;

            $search_term = sanitize_text_field(get_search_query());
            $request_ip = static::resolve_request_ip();

            if ( ! empty( $search_term ) && ! static::should_skip_tracking($search_term, $request_ip) ) {
                $wpdb->insert(
                    $wpdb->prefix . 'search_tracker_logs',
                    [
                        'search_term' => $search_term,
                        'user_id'     => get_current_user_id(),
                        'ip_address'  => $request_ip,
                        'created_at'  => current_time('mysql')
                    ]
                );
            }
        }
    }

    public static function get_search_terms_and_courses()
    {
        $search_term = static::resolve_search_term();
        $course_slug = static::resolve_course_slug();
        $request_ip = static::resolve_request_ip();

        if ( ! empty($search_term) && ! empty($course_slug) && ! static::should_skip_tracking($search_term, $request_ip) ) {
            global $wpdb;

            $wpdb->insert(
                $wpdb->prefix . 'search_tracker_logs',
                [
                    'search_term'   => $search_term,
                    'search_course' => $course_slug,
                    'user_id'       => get_current_user_id(),
                    'ip_address'    => $request_ip,
                    'created_at'    => current_time('mysql')
                ]
            );
        }
    }

    protected static function should_skip_tracking($search_term, $request_ip)
    {
        $filters = static::get_tracking_filters();
        $normalizedIp = trim((string) $request_ip);

        if ($normalizedIp !== '' && in_array($normalizedIp, $filters['ips'], true)) {
            return true;
        }

        foreach ($filters['words'] as $word) {
            if ($word !== '' && stripos($search_term, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    protected static function get_tracking_filters()
    {
        if (is_array(static::$filterCache)) {
            return static::$filterCache;
        }

        $filterMeta = FilterMeta::getInstance();
        $ipMeta = $filterMeta->getMetaValue(static::META_FILTER_IP, '');
        $wordMeta = $filterMeta->getMetaValue(static::META_FILTER_WORD, '');

        static::$filterCache = [
            'ips' => static::parse_csv_values($ipMeta),
            'words' => static::parse_csv_values($wordMeta),
        ];

        return static::$filterCache;
    }

    protected static function parse_csv_values($value)
    {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $value));
        $parts = array_values(array_filter($parts, function ($item) {
            return $item !== '';
        }));

        return array_values(array_unique($parts));
    }

    protected static function resolve_request_ip()
    {
        if (empty($_SERVER['REMOTE_ADDR'])) {
            return '';
        }

        return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    }

    protected static function resolve_search_term()
    {
        if (isset($_GET['sq']) && $_GET['sq'] !== '') {
            return sanitize_text_field(wp_unslash($_GET['sq']));
        }

        if (!empty($_SERVER['REQUEST_URI'])) {
            $query = (string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_QUERY);
            if (!empty($query)) {
                parse_str($query, $query_vars);
                if (!empty($query_vars['sq'])) {
                    return sanitize_text_field(wp_unslash($query_vars['sq']));
                }
            }
        }

        return '';
    }

    protected static function resolve_course_slug()
    {
        global $post;

        if ($post instanceof \WP_Post && !empty($post->post_name)) {
            return sanitize_title($post->post_name);
        }

        $queried_object = get_queried_object();
        if ($queried_object instanceof \WP_Post && !empty($queried_object->post_name)) {
            return sanitize_title($queried_object->post_name);
        }

        $queried_id = get_queried_object_id();
        if (!empty($queried_id)) {
            $post_name = (string) get_post_field('post_name', $queried_id);
            if (!empty($post_name)) {
                return sanitize_title($post_name);
            }
        }

        if (!empty($_SERVER['REQUEST_URI'])) {
            $path = (string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
            $slug = basename(untrailingslashit($path));
            if (!empty($slug)) {
                return sanitize_title($slug);
            }
        }

        return '';
    }
}
