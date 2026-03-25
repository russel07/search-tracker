<?php

namespace SearchTracker\Rus\Hooks\Handler;

use SearchTracker\Rus\Foundation\AppHelper;

class ActionHooksHandler
{
    use AppHelper;

    public static function get_search_terms_and_keywords()
    {
        if ( is_search() ) {
            global $wpdb;

            $search_term = get_search_query();

            if ( ! empty( $search_term ) ) {
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

    public static function get_search_terms_and_courses()
    {
        $search_term = static::resolve_search_term();
        $course_slug = static::resolve_course_slug();

        if ( ! empty($search_term) && ! empty($course_slug) ) {
            global $wpdb;

            $wpdb->insert(
                $wpdb->prefix . 'search_tracker_logs',
                [
                    'search_term'   => $search_term,
                    'search_course' => $course_slug,
                    'user_id'       => get_current_user_id(),
                    'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? '',
                    'created_at'    => current_time('mysql')
                ]
            );
        }
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
