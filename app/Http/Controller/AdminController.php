<?php

namespace SearchTracker\Rus\Http\Controller;

use SearchTracker\Rus\Http\Request\ValidatorHandler;
use SearchTracker\Rus\Http\Model\SearchTracker;

class AdminController extends BaseController
{
    public function index(\WP_REST_Request $request)
    {
        $page   = intval(sanitize_text_field($request->get_param('page')));
        $limit  = intval(sanitize_text_field($request->get_param('per_page')));
        if( ! $limit ) {
            $limit = 10;
        }

        if( ! $page ) {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;

        $searchTracker = SearchTracker::getInstance();
        $total_items = count($searchTracker::all(null, null, ''));
        $searchTrackerData = $searchTracker::all($limit, $offset, '');

        return $this->response([
            'total_items' => $total_items,
            'data' => $searchTrackerData,
            'current_page' => $page,
            'per_page' => $limit
        ]);
    }

    public function deleteData(\WP_REST_Request $request)
    {
        // Verify user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->response( 'Permission denied', 403 );
		}

        $ids = $request->get_param('ids');
        foreach ($ids as $id) {
        $searchTracker = SearchTracker::getInstance();
            $searchTracker->destroy($id);
        }

        return $this->response([
            'message' => 'Selected search terms deleted successfully.'
        ]);
    }

    public function exportData(\WP_REST_Request $request)
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->response( 'Permission denied', 403 );
        }

        $searchTracker = SearchTracker::getInstance();
        $condition = $this->buildExportCondition($request);
        $data = $searchTracker::all(null, 0, $condition);

        $csvWriter = new \SearchTracker\Rus\Services\CsvWriter();
        $csvWriter->insertAll($this->formatExportRows($data));
        $csvWriter->output('search_terms.csv');
    }

    public function exportSelectedData(\WP_REST_Request $request)
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->response( 'Permission denied', 403 );
        }

        $ids = (array) $request->get_param('ids');
        $ids = array_values(array_filter(array_map('absint', $ids)));

        if (empty($ids)) {
            return $this->response('No valid records selected for export.', 422);
        }

        $searchTracker = SearchTracker::getInstance();
        $condition = 'id IN (' . implode(',', $ids) . ')';
        $data = $searchTracker::all(null, 0, $condition);

        $csvWriter = new \SearchTracker\Rus\Services\CsvWriter();
        $csvWriter->insertAll($this->formatExportRows($data));
        $csvWriter->output('selected_search_terms.csv');
    }

    protected function buildExportCondition(\WP_REST_Request $request)
    {
        global $wpdb;
        $conditions = [];

        $search = $request->get_param('search');
        if (!empty($search)) {
            $search = sanitize_text_field($search);
            $like = '%' . $wpdb->esc_like($search) . '%';
            $conditions[] = $wpdb->prepare("search_term LIKE %s", $like);
        }

        $dateRange = $request->get_param('date_range');
        if (is_array($dateRange) && count($dateRange) === 2 && !empty($dateRange[0]) && !empty($dateRange[1])) {
            // Parse date strings in YYYY-MM-DD format
            $startDate = sanitize_text_field($dateRange[0]);
            $endDate = sanitize_text_field($dateRange[1]);

            // Validate date format
            if ($this->isValidDate($startDate) && $this->isValidDate($endDate)) {
                $startDateTime = $startDate . ' 00:00:00';
                $endDateTime = $endDate . ' 23:59:59';
                $conditions[] = "created_at >= '" . $startDateTime . "' AND created_at <= '" . $endDateTime . "'";
            }
        }

        return !empty($conditions) ? implode(' AND ', $conditions) : '';
    }

    protected function isValidDate($date)
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
    }

    protected function formatExportRows($data)
    {
        $rows = [[
            'Search Content',
            'Courses',
            'Last Searched',
            'IP Address'
        ]];

        foreach ((array) $data as $row) {
            $item = is_object($row) ? get_object_vars($row) : (array) $row;

            $rows[] = [
                isset($item['search_term']) ? (string) $item['search_term'] : '',
                isset($item['search_course']) ? (string) $item['search_course'] : '',
                isset($item['created_at']) ? (string) $item['created_at'] : '',
                isset($item['ip_address']) ? (string) $item['ip_address'] : ''
            ];
        }

        return $rows;
    }
}
