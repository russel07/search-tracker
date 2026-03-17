<?php

namespace SearchTracker\Rus\Services;

use SearchTracker\Rus\Foundation\AppHelper;

class Mailer
{
    use AppHelper;

    public static function get_from_name()
    {
        $from_name = get_option('woocommerce_email_from_name');
        return wp_specialchars_decode(esc_html($from_name), ENT_QUOTES);
    }

    public static function get_from_address()
    {
        $from_email = get_option('woocommerce_email_from_address');
        return sanitize_email($from_email);
    }

    public static function send_gift_email($email_data)
    {
        [
            'receiver_name'    => $receiver_name,
            'sender_name'      => $sender_name,
            'selected_image'   => $selected_image,
            'gift_message'     => $gift_message,
            'gift_amount'      => $gift_amount,
            'brand_name'       => $brand_name,
            'brand_logo'       => $brand_logo,
            'gift_code'        => $gift_code,
            'to_email'         => $to_email,
            'redeem_gift_page' => $redeem_gift_page
        ] = $email_data;

        $currency_symbols = self::get_gift_card_currency_symbols();
        $currency = get_option('woocommerce_currency');
        $currency_symbol = isset($currency_symbols[$currency]) ? $currency_symbols[$currency] : '';

        // Start output buffering
        ob_start();

        // Include the email template file, passing the data into it
        include_once(dirname(SA_E_GIFT_CARD_PLUGIN_PATH) . '/app/views/email.php');

        // Get the contents of the buffer and store it in the $message variable
        $message = ob_get_clean();

        // Set email subject
        $subject = "You Have Received a Gift Card!";

        // Set headers for HTML email
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . self::get_from_name() . ' <' . self::get_from_address() . '>',
            'From: ' . self::get_from_name() . ' <' . self::get_from_address() . '>'
        );

        // Attempt to send the email and log errors if any
        if (!wp_mail($to_email, $subject, $message, $headers)) {
            rus_mylog("Failed to send gift card email to $to_email for gift code $gift_code.");
        }
    }

    public static function send_email_to_gift_sender($email_data)
    {
        [
            'receiver_name'    => $receiver_name,
            'receiver_email'   => $receiver_email,
            'sender_name'      => $sender_name,
            'purchased_date'   => $purchased_date,
            'delivery_date'    => $delivery_date,
            'selected_image'   => $selected_image,
            'gift_message'     => $gift_message,
            'gift_amount'      => $gift_amount,
            'brand_name'       => $brand_name,
            'brand_logo'       => $brand_logo,
            'to_email'         => $to_email
        ] = $email_data;

        $currency_symbols = self::get_gift_card_currency_symbols();
        $currency = get_option('woocommerce_currency');
        $currency_symbol = isset($currency_symbols[$currency]) ? $currency_symbols[$currency] : '';

        // Start output buffering
        ob_start();

        // Include the email template file, passing the data into it
        include_once(dirname(SA_E_GIFT_CARD_PLUGIN_PATH) . '/app/views/sender-email.php');

        // Get the contents of the buffer and store it in the $message variable
        $message = ob_get_clean();

        // Set email subject
        $subject = "Your Gift Card Delivered!";

        // Set headers for HTML email
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . self::get_from_name() . ' <' . self::get_from_address() . '>',
            'From: ' . self::get_from_name() . ' <' . self::get_from_address() . '>'
        );

        // Attempt to send the email and log errors if any
        if (!wp_mail($to_email, $subject, $message, $headers)) {
            rus_mylog("Failed to send gift card email to $to_email for gift code $gift_code.");
        }
    }

}
