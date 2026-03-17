<?php

namespace SearchTracker\Rus\Http\Controller;

use SearchTracker\Rus\Foundation\AppHelper;
use SearchTracker\Rus\Http\Request\Request;

class BaseController
{
    use AppHelper;

    /**
     * Static property to hold the instance of the class.
     *
     * @var static|null
     */
    protected static $instance;

    /**
     * Properties to hold the request and validator handler.
     *
     * @var Request|null
     * @var mixed|null
     */
    protected $request = null;

    /**
     * Constructor to initialize the request object.
     */
    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Static method to get the instance of the class.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Method to generate a response object.
     *
     * @param mixed $data    The data to be included in the response.
     * @param int   $status  The HTTP status code for the response.
     *
     * @return \WP_REST_Response
     */
    protected function response($data, $status = 200)
    {
        return new \WP_REST_Response(
            $data,
            $status
        );
    }

    /**
     * Method to generate an error object.
     *
     * @param string $message  The error message.
     *
     * @return \WP_Error
     */
    protected function error($message)
    {
        return new \WP_Error(
            403,
            $message
        );
    }
}