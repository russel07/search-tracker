<?php

namespace SearchTracker\Rus\Http\Request;

class ValidatorHandler extends Validator
{
    /**
     * Validate add to cart Data
     *
     * @param Request $request The request object.
     *
     * @return array List of validation errors.
     */
    public static function validateIds($request)
    {
        return $request->validate(
            $request->all(),
            [
                'ids' => 'required|array',
                'ids.*' => 'required|integer'
            ],
            [
                'ids.required' => __('IDs are required', 'search-tracker'),
                'ids.array' => __('IDs must be an array', 'search-tracker'),
                'ids.*.required' => __('Each ID is required', 'search-tracker'),
                'ids.*.integer' => __('Each ID must be an integer', 'search-tracker')
            ]
        );
    }
}