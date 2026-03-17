<?php

namespace SearchTracker\Rus\Http\Controller;

use SearchTracker\Rus\Http\Model\GiftEvent;

class SettingsController extends BaseController
{
    /**
     * Retrieve and return plugin settings.
     *
     * @return mixed Response data.
     */
    public function index(\WP_REST_Request $request)
    {
        $options = GiftEvent::getRandTenItem();
        $occasions = self::get_event_list();

        return $this->response([
            'options' => $options,
            'occasions' => $occasions,
        ]);
    }

    public function design_list()
    {
        $giftEvent = GiftEvent::getInstance();

        $eventData = $giftEvent::all();

        $occasions = self::get_event_list();

        return $this->response([
            'design_list' => $eventData,
            'occasions' => $occasions,
        ]);
    }
}
