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
    public static function validateCartData($request)
    {
        return $request->validate(
            $request->all(),
            [
                'gift_amount' => 'required',
                'recipient_name' => 'required',
                'recipient_email' => 'required|email',
                'sender_name' => 'required',
                'delivery_date' => 'required',
                'product_id' => 'required'
            ],
            [
                'gift_amount.required' => __('Receiver name is required', 'e-gift-card-sa'),
                'recipient_name.required' => __('Receiver name is required', 'e-gift-card-sa'),
                'recipient_email.required'    => __('Receiver email is required', 'e-gift-card-sa'),
                'recipient_email.email'    => __('Receiver email is not a valid email', 'e-gift-card-sa'),
                'sender_name.required' => __('Sender name is required', 'e-gift-card-sa'),
                'delivery_date.required' => __('Delivery Date is required', 'e-gift-card-sa'),
                'product_id.required' => __('Gift Product is Required, set from general settings', 'e-gift-card-sa'),
            ]
        );
    }

    public static function validateGeneralSettings ( $request ) 
    {
        return $request->validate(
            $request->all(),
            [
                'gift_card_page_id' => 'required',
                'redeem_gift_page_id' => 'required',
                'gift_product_id' => 'required',
                'gift_amount' => 'required',
            ],
            [
                'gift_card_page_id.required' => __('Gift Card Page is required', 'e-gift-card-sa'),
                'redeem_gift_page_id.required' => __('Redeem Gift Page is required', 'e-gift-card-sa'),
                'gift_product_id.required' => __('Gift Card Product is required', 'e-gift-card-sa'),
                'gift_amount.required' => __('Gift Amount is required', 'e-gift-card-sa'),
            ]
        );
    }
    
    public static function validateEventDesignRequest ( $request ) 
    {
        return $request->validate(
            $request->all(),
            [
                'event_name' => 'required',
                'design_name' => 'required',
                'event_file_type' => 'required'
            ],
            [
                'event_name.required' => __('Event Name is required', 'e-gift-card-sa'),
                'design_name.required' => __('Design Name is required', 'e-gift-card-sa'),
                'event_file_type.required' => __('File Type is required', 'e-gift-card-sa'),
            ]
        );
    }
}