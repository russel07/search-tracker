<?php

namespace SearchTracker\Rus\Http\Request;

/**
 * Class Validator
 *
 * Validates data based on specified rules and error messages.
 */
class Validator
{
    private $data     = [];
    private $rules    = [];
    private $messages = [];
    private $errors   = [];

    /**
     * Validator constructor.
     *
     * @param array $data     The data to validate.
     * @param array $rules    The validation rules.
     * @param array $messages Custom error messages.
     */
    public function __construct($data, $rules, $messages)
    {
        $this->data     = $data;
        $this->messages = $messages;
        $this->formatValidatorData($rules);
    }

    /**
     * Validate the data.
     *
     * @return array List of validation errors.
     */
    public function validate()
    {
        foreach ($this->rules as $field => $ruleStr) {
            $rulesArr = explode('|', $ruleStr);
            foreach ($rulesArr as $rule) {
                if (method_exists($this, $rule)) {
                    $this->{$rule}($field);
                }
            }
        }

        return $this->errors;
    }

    /**
     * Check if a field is required.
     *
     * @param string $field The field name.
     */
    private function required($field)
    {
        if ( ! isset($this->data[$field]) || empty($this->data[$field])) {
            $this->errors[$field] = $this->getMessage("$field.required", "$field is required");
        }
    }

    /**
     * Check if a field is a valid email.
     *
     * @param string $field The field name.
     */
    private function email($field)
    {
        if (!is_email($this->data[$field])) {
            $this->errors[$field] = $this->getMessage("$field.email", "$field is not a valid email");
        }
    }

    /**
     * Check if a field is a valid array.
     *
     * @param string $field The field name.
     */
    private function array($field)
    {
        if (!is_array($this->data[$field])) {
            $this->errors[$field] = $this->getMessage("$field.array", "$field should be an array");
        }
    }

    /**
     * Format validation data.
     *
     * @param array $rules The validation rules.
     */
    private function formatValidatorData($rules)
    {
        foreach ($rules as $field => $ruleStr) {
            $this->rules[$field] = $ruleStr;
        }
    }

    /**
     * Get a custom error message or return a default message.
     *
     * @param string $messageKey The message key.
     * @param string $default    The default message.
     *
     * @return string The error message.
     */
    private function getMessage($messageKey, $default)
    {
        if (isset($this->messages[$messageKey])) {
            return $this->messages[$messageKey];
        }

        return $default;
    }
}