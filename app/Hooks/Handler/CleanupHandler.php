<?php

namespace SearchTracker\Rus\Hooks\Handler;


/**
 * Class CleanupHandler
 *
 * Handles plugin scheduled tasks.
 */
class CleanupHandler
{
    /**
     * Delete uploaded files that are older than a month.
     */
    public static function may_be_delete_uploaded_files()
    {
        // Get the current date in 'Y-m-d' format
        $now = date('Y-m-d');
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . SEARCH_TRACKER_ASSET_ID . DIRECTORY_SEPARATOR . '_temp';

        // Check if the upload directory exists
        if (file_exists($upload_path)) {
            // Get all files in the upload directory
            $files = scandir($upload_path);

            // Loop through each file
            foreach ($files as $file) {
                // Skip the '.' and '..' directories
                if ($file === '.' || $file === '..') {
                    continue;
                }

                // Get the file's last modified date
                $last_modified = date('Y-m-d', filemtime($upload_path . DIRECTORY_SEPARATOR . $file));

                // Calculate the difference between the current date and the file's last modified date
                $diff = abs(strtotime($now) - strtotime($last_modified));

                // Check if the file is older than a month
                if ($diff > 30 * 24 * 60 * 60) {
                    // Delete the file
                    unlink($upload_path . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
    }
}