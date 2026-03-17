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
}
