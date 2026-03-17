<?php
namespace SearchTracker\Rus\Http\Router;
use WP_REST_Server;
class Router
{
    /**
     * Register a new route for GET requests.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     */
    public static function get($url, $action)
    {
        self::registerRoute($url, $action, 'GET');
    }

    /**
     * Register a new route for POST requests.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     */
    public static function post($url, $action)
    {
        self::registerRoute($url, $action, WP_REST_Server::CREATABLE);
    }

    /**
     * Register a new route for PUT requests.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     */
    public static function put($url, $action)
    {
        self::registerRoute($url, $action, WP_REST_Server::EDITABLE);
    }

    /**
     * Register a new route for DELETE requests.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     */
    public static function delete($url, $action)
    {
        self::registerRoute($url, $action, WP_REST_Server::DELETABLE);
    }

    /**
     * Register a new route.
     *
     * @param string $url The route URL.
     * @param string $action The controller method in the format "Controller@method".
     * @param string $type The HTTP request type (e.g., READABLE, CREATABLE, EDITABLE, DELETABLE).
     */
    private static function registerRoute($url, $action, $type)
    {
        list($controller, $method) = self::parseAction($action);

        if ( ! class_exists( $controller ) ) {
            return;
        }

        $controllerInstance = new $controller();

        if ( ! method_exists( $controllerInstance, $method ) ) {
            return;
        }

        register_rest_route(
            SEARCH_TRACKER_TEXT_DOMAIN . '/v2',
            $url,
            [
                'methods' => $type,
                'callback' => [$controllerInstance, $method],
            ]
        );
    }

    /**
     * Parse the controller and method from the action string.
     *
     * @param string $action The action in the format "Controller@method".
     * @return array [$controller, $method]
     */
    private static function parseAction($action) {
        list($controller, $method) = explode('@', $action);
        $controller = 'SearchTracker\\Rus\\Http\\Controller\\' . $controller;
        return array($controller, $method);
    }
}