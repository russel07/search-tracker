<?php

namespace SearchTracker\Rus\Http\Controller;

use SearchTracker\Rus\Http\Model\FilterMeta;
use SearchTracker\Rus\Http\Request\ValidatorHandler;
use SearchTracker\Rus\Http\Model\SearchTracker;

class AdminController extends BaseController
{
    const META_FILTER_IP = 'FILTER_IP';
    const META_FILTER_WORD = 'FILTER_WORD';

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
        $condition = $this->buildFilterCondition($request);
        $total_items = count($searchTracker::all(null, null, $condition));
        $searchTrackerData = $searchTracker::all($limit, $offset, $condition);

        return $this->response([
            'total_items' => $total_items,
            'data' => $searchTrackerData,
            'current_page' => $page,
            'per_page' => $limit
        ]);
    }

    public function getSettings(\WP_REST_Request $request)
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->response( 'Permission denied', 403 );
        }

        $filterMeta = FilterMeta::getInstance();

        return $this->response([
            'filter_ips' => $filterMeta->getMetaValue(self::META_FILTER_IP, ''),
            'filter_words' => $filterMeta->getMetaValue(self::META_FILTER_WORD, ''),
        ]);
    }

    public function saveSettings(\WP_REST_Request $request)
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->response( 'Permission denied', 403 );
        }

        $rawIps = (string) $request->get_param('filter_ips');
        $rawWords = (string) $request->get_param('filter_words');

        $ipValues = $this->normalizeCommaSeparatedValues($rawIps);
        $wordValues = $this->normalizeCommaSeparatedValues($rawWords);

        $invalidIps = $this->findInvalidIps($ipValues);
        if (!empty($invalidIps)) {
            return $this->response([
                'message' => 'Invalid IP address(es): ' . implode(', ', $invalidIps)
            ], 422);
        }

        $safeWords = array_map('sanitize_text_field', $wordValues);
        $safeWords = array_values(array_filter($safeWords, function ($item) {
            return $item !== '';
        }));

        $filterMeta = FilterMeta::getInstance();
        $filterMeta->setMetaValue(self::META_FILTER_IP, implode(', ', $ipValues));
        $filterMeta->setMetaValue(self::META_FILTER_WORD, implode(', ', $safeWords));

        return $this->response([
            'message' => 'Settings saved successfully.',
            'filter_ips' => implode(', ', $ipValues),
            'filter_words' => implode(', ', $safeWords),
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

    public function deleteAllData(\WP_REST_Request $request)
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->response( 'Permission denied', 403 );
        }

        $searchTracker = SearchTracker::getInstance();
        $condition = $this->buildFilterCondition($request);
        $rows = (array) $searchTracker::all(null, 0, $condition);

        $deleted = 0;
        foreach ($rows as $row) {
            $item = is_object($row) ? get_object_vars($row) : (array) $row;
            $id = isset($item['id']) ? absint($item['id']) : 0;
            if ($id > 0 && $searchTracker->destroy($id) !== false) {
                $deleted++;
            }
        }

        return $this->response([
            'message' => 'Search terms deleted successfully.',
            'deleted_count' => $deleted
        ]);
    }

    public function exportData(\WP_REST_Request $request)
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->response( 'Permission denied', 403 );
        }

        $searchTracker = SearchTracker::getInstance();
        $condition = $this->buildFilterCondition($request);
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

    protected function buildFilterCondition(\WP_REST_Request $request)
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
            $startDate = sanitize_text_field($dateRange[0]);
            $endDate = sanitize_text_field($dateRange[1]);
            $dateCondition = $this->buildDateRangeCondition($startDate, $endDate);
            if (!empty($dateCondition)) {
                $conditions[] = $dateCondition;
            }
        }

        $dateFrom = sanitize_text_field((string) $request->get_param('date_from'));
        $dateTo = sanitize_text_field((string) $request->get_param('date_to'));
        if (!empty($dateFrom) && !empty($dateTo)) {
            $dateCondition = $this->buildDateRangeCondition($dateFrom, $dateTo);
            if (!empty($dateCondition)) {
                $conditions[] = $dateCondition;
            }
        }

        return !empty($conditions) ? implode(' AND ', $conditions) : '';
    }

    protected function buildDateRangeCondition($startDate, $endDate)
    {
        global $wpdb;

        if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
            return '';
        }

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        return $wpdb->prepare('created_at >= %s AND created_at <= %s', $startDateTime, $endDateTime);
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

    protected function normalizeCommaSeparatedValues($value)
    {
        if (!is_string($value) || $value === '') {
            return [];
        }

        $parts = explode(',', $value);
        $normalized = [];

        foreach ($parts as $part) {
            $item = trim(wp_strip_all_tags($part));
            if ($item !== '') {
                $normalized[] = $item;
            }
        }

        return array_values(array_unique($normalized));
    }

    protected function findInvalidIps($ips)
    {
        $invalid = [];

        foreach ((array) $ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                $invalid[] = $ip;
            }
        }

        return $invalid;
    }
}
