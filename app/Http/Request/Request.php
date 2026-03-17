<?php
namespace SearchTracker\Rus\Http\Request;

/**
 * Class Request
 *
 * Represents an HTTP request.
 */
class Request
{
    /**
     * Get all request parameters.
     *
     * @return array
     */
    public function all()
    {
        return $_REQUEST;
    }

    /**
     * Get a specific request parameter by key.
     *
     * @param string $key      The key of the request parameter.
     * @param mixed  $default  The default value to return if the key is not found.
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!isset($_REQUEST[$key])) {
            return $default;
        }

        return $_REQUEST[$key];
    }

    /**
     * Magic method to get a specific request parameter by key.
     *
     * @param string $key The key of the request parameter.
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Get a specific request parameter safely by key.
     *
     * @param string $key The key of the request parameter.
     *
     * @return mixed|null
     */
    public function getSafe($key)
    {
        if (!isset($_REQUEST[$key])) {
            return null;
        }

        return $_REQUEST[$key];
    }

    /**
     * Validate request data using specified rules and custom error messages.
     *
     * @param array $data     The data to validate.
     * @param array $rules    The validation rules.
     * @param array $messages Custom error messages.
     *
     * @return array
     */
    public function validate($data, $rules, $messages)
    {
        $validator = new Validator($data, $rules, $messages);

        return $validator->validate();
    }
}