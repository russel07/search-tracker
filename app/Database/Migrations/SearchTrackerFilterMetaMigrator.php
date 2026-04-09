<?php

namespace SearchTracker\Rus\Database\Migrations;

class SearchTrackerFilterMetaMigrator
{
    protected static $tableName = 'search_tracker_filter_meta';

    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . esc_sql(static::$tableName);

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {
            $sql = "CREATE TABLE $table (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `meta` VARCHAR(100) NOT NULL DEFAULT '',
                `meta_value` LONGTEXT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `meta` (`meta`)
            ) $charsetCollate;";

            dbDelta($sql);
        }
    }
}
