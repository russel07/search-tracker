<?php

namespace SearchTracker\Rus\Foundation;

trait AppHelper
{
    public static function e_gift_card_app_vars()
    {
        $upload_dir         = wp_upload_dir();
        $uploadUrl          = esc_url($upload_dir['baseurl']) . DIRECTORY_SEPARATOR . SEARCH_TRACKER_ASSET_ID;
        $title              = get_bloginfo('name') . __(' PLUGIN Starter', SEARCH_TRACKER_TEXT_DOMAIN);
        $sub_title          = get_bloginfo('name') . __(' PLUGIN Starter', SEARCH_TRACKER_TEXT_DOMAIN);
        $post_page_link     = admin_url('post.php');

        return array(
            'ajax_url'      => admin_url('admin-ajax.php'),
            'slug'          => SEARCH_TRACKER_TEXT_DOMAIN,
            'page_title'    => $title,
            'sub_title'     => $sub_title,
            'plugin_assets' => SEARCH_TRACKER_URL . 'assets/',
            'image_url'     => $uploadUrl,
            'post_page_link' => $post_page_link,
            'rest_info'     => array(
                'base_url'  => esc_url_raw(rest_url()),
                'rest_url'  => rest_url(SEARCH_TRACKER_TEXT_DOMAIN . '/v2'),
                'nonce'     => wp_create_nonce('wp_rest'),
                'namespace' => SEARCH_TRACKER_TEXT_DOMAIN,
                'version'   => 'v2',
            ),
        );
    }

    /**
     * getOption method will return settings using key
     * This method will get a key as parameter, fetch data from the database, and return it.
     *
     * @param string $key The option key to retrieve.
     * @param mixed $default The default value to return if the option is not found.
     * @return mixed The option value or the default value.
     */
    public static function getOption($key, $default = '')
    {
        // Get settings from options table using the key
        $data = get_option($key);

        // If the data exists, return it; otherwise, return the default value
        return $data !== false ? maybe_unserialize($data) : $default;
    }

    /**
     * Update or insert an option in the wp_options table.
     * This method serializes the data if necessary.
     * 
     * @param string $key   The option key to update.
     * @param mixed  $value The value to store.
     * @return bool True if the value was updated successfully, false otherwise.
     */
    public static function updateOption($key, $value)
    {
        // Serialize the value if it's an array or object.
        return update_option($key, maybe_serialize($value));
    }


    /**
     * Retrieve all published WordPress pages and format them as an array.
     *
     * This function queries the WordPress database to retrieve all pages 
     * with a post type of 'page' and a post status of 'publish'. 
     * It returns an array of formatted pages containing their ID and title.
     *
     * @return array $formattedPages An array of formatted pages with 'id' and 'title'.
     */
    public static function getWPPages()
    {
        global $wpdb;

        // Query to get all published pages
        $pages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title 
                FROM {$wpdb->prefix}posts 
                WHERE post_type = %s 
                AND post_status = %s 
                ORDER BY ID DESC",
                'page',
                'publish'
            )
        );

        // Initialize the formatted pages array
        $formattedPages = [];

        // Loop through each page and format the output
        foreach ($pages as $page) {
            $formattedPages[] = [
                'id'    => strval($page->ID),   // Convert ID to string as per requirement
                'title' => $page->post_title
            ];
        }

        return $formattedPages;
    }


    /**
     * Retrieve all published WooCommerce products and format them as an array.
     *
     * This function queries the WordPress database to retrieve all products 
     * (post type 'product') with a post status of 'publish'. 
     * It returns an array of formatted products containing their ID and title.
     *
     * @return array $formattedProducts An array of formatted WooCommerce products with 'id' and 'title'.
     */
    public static function getWooCommerceProducts()
    {
        global $wpdb;

        // Query to get all published WooCommerce products
        $products = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title 
                FROM {$wpdb->prefix}posts 
                WHERE post_type = %s 
                AND post_status = %s 
                ORDER BY ID DESC",
                'product',
                'publish'
            )
        );

        // Initialize the formatted products array
        $formattedProducts = [];

        // Loop through each product and format the output
        foreach ($products as $product) {
            $formattedProducts[] = [
                'id'    => strval($product->ID),   // Convert ID to string as per requirement
                'title' => $product->post_title
            ];
        }

        return $formattedProducts;
    }

    /**
     * Handle file uploads.
     *
     * @param string $file_key
     * @return string|WP_Error The file name of the uploaded file, or WP_Error on failure
     */
    public static function handle_file_upload($file_key, $upload_directory = SEARCH_TRACKER_ASSET_ID)
    {
        // Ensure that wp_handle_upload is available
        if (! function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] != UPLOAD_ERR_OK) {
            return new WP_Error('file_upload_error', 'File upload failed.');
        }

        // Get the WordPress upload directory
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $upload_directory;

        // Ensure the directory exists, if not, create it
        if (!file_exists($upload_path)) {
            wp_mkdir_p($upload_path);
        }

        // Customize the file upload to the new directory
        $file = $_FILES[$file_key];
        $file_name = sanitize_file_name($file['name']);
        // Generate a unique prefix (e.g., timestamp + random number)
        $unique_prefix = uniqid('egc_', true);

        // Add the unique prefix to the file name
        $new_file_name = $unique_prefix . '_' . $file_name;
        $new_file_path = $upload_path . '/' . $new_file_name;

        // Move the uploaded file to the new directory
        if (!move_uploaded_file($file['tmp_name'], $new_file_path)) {
            return new WP_Error('move_upload_error', 'Failed to move the uploaded file.');
        }

        // Return just the file name
        return $new_file_name;
    }
}
