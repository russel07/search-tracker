<?php


namespace SearchTracker\Rus\Database\Migrations;

class SearchTrackerLogsMigrator
{
    protected static $tableName = 'search_tracker_logs';

    public static function migrate()
    {
        global $wpdb;

        // Get charset and collation for the table
        $charsetCollate = $wpdb->get_charset_collate();

        // Escape the table name properly
        $table = $wpdb->prefix . esc_sql(static::$tableName);

        // Check if the table already exists
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {

            // SQL query to create the table if it doesn't exist
            $sql = "CREATE TABLE $table (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `search_term` VARCHAR(255) NOT NULL DEFAULT '',
                `search_course` VARCHAR(255) NULL DEFAULT NULL,
                `user_id` BIGINT(20) UNSIGNED NULL,
                `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) $charsetCollate;";
            
            dbDelta($sql);
        }
    }
}
