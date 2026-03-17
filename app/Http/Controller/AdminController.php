<?php

namespace SearchTracker\Rus\Http\Controller;

use SearchTracker\Rus\Http\Request\ValidatorHandler;
use SearchTracker\Rus\Http\Model\GiftCode;

class AdminController extends BaseController
{
    public function index(\WP_REST_Request $request)
    {
        $page   = intval(sanitize_text_field($request->get_param('page')));
        $limit  = intval(sanitize_text_field($request->get_param('per_page')));
        $offset = ($page - 1) * $limit;

        $giftCode = GiftCode::getInstance();
        $total_items = count($giftCode::all(null, null, 'order_id != 0'));
        $giftCodeData = $giftCode::all($limit, $offset, 'order_id != 0');

        return $this->response([
            'total_items' => $total_items,
            'gift_codes' => $giftCodeData,
            'current_page' => $page,
            'per_page' => $limit
        ]);
    }

    /**
     * Return quick overview statistics for WPLMS.
     */
    public function overview(\WP_REST_Request $request)
    {
        global $wpdb;

        $data = [];
        // post counts for various CPTs
        $data['total_courses']    = $this->count_posts_by_type('course');
        $data['total_units']      = $this->count_posts_by_type('unit');
        $data['total_quizzes']    = $this->count_posts_by_type('quiz');
        $data['total_questions']  = $this->count_posts_by_type('question');
        $data['total_media']      = $this->count_posts_by_type('attachment');

        // database size (bytes)
        $schema = $wpdb->dbname; echo "SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = '{$schema}'";
        $size   = $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = '{$schema}'");
        $data['db_size']          = size_format(intval($size));

        // uploads folder size
        $uploads       = wp_upload_dir();
        $data['upload_size']      = size_format($this->get_directory_size($uploads['basedir']));

        // potential cleanup = size of plugin temp folder
        $tempDir = trailingslashit($uploads['basedir']) . 'search-tracker/_temp';
        $data['potential_cleanup'] = size_format($this->get_directory_size($tempDir));

        return $this->response($data);
    }

    /**
     * Run a full scan and store the last report.
     */
    public function runFullScan(\WP_REST_Request $request)
    {
        $scan = [
            'draft_courses' => $this->scan_draft_courses(),
            'orphan_units' => $this->scan_orphan_units(),
            'orphan_quizzes' => $this->scan_orphan_quizzes(),
            'unused_media' => $this->scan_unused_media(),
            'empty_terms' => $this->scan_empty_terms()
        ];

        update_option('wplms_cleanup_last_report', $scan);

        return $this->response([
            'message' => 'scan_complete',
            'report' => $scan
        ]);
    }

    /**
     * Retrieve the last saved report, if any.
     */
    public function lastReport(\WP_REST_Request $request)
    {
        $report = get_option('wplms_cleanup_last_report', []);
        return $this->response(['report' => $report]);
    }

    /**
     * Helper for counting published posts for a given post type.
     */
    protected function count_posts_by_type($type)
    {
        $args = [
            'post_type'      => $type,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ];
        $posts = get_posts($args);
        return is_array($posts) ? count($posts) : 0;
    }

    /**
     * Recursively calculate directory size in bytes.
     */
    protected function get_directory_size($dir)
    {
        $size = 0;
        if (! is_dir($dir)) {
            return 0;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            $size += $file->getSize();
        }
        return $size;
    }
}
