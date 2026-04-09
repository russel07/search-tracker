<?php

namespace SearchTracker\Rus\Database;

use SearchTracker\Rus\Database\Migrations\SearchTrackerFilterMetaMigrator;
use SearchTracker\Rus\Database\Migrations\SearchTrackerLogsMigrator;

require_once(ABSPATH.'wp-admin/includes/upgrade.php');

class DBMigrate
{
    protected static $migrators = [
        SearchTrackerLogsMigrator::class,
        SearchTrackerFilterMetaMigrator::class,
    ];

    public static function run(){
        self::migrate();
    }

    public static function migrate()
    {
        foreach (self::$migrators as $class) {
            $class::migrate();
        }
    }
}