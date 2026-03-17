<?php


use SearchTracker\Rus\Http\Router\Router;

//Route for customizer for user
Router::get('general-settings', 'SettingsController@index');
Router::get('get-all-design', 'SettingsController@design_list');
//Route to add to cart and update cart.
Router::post('add-to-cart', 'CartController@add');
Router::post('update-cart', 'CartController@update');

//Admin Features 
Router::get('admin-settings', 'AdminController@general_settings');
Router::post('update-settings', 'AdminController@update_settings');
Router::get('gift-code-list', 'AdminController@index');
Router::get('get-event-info', 'EventDesignController@index');
Router::post('add-event-info', 'EventDesignController@add_event_info');
Router::post('update-event-info', 'EventDesignController@update_event_info');
Router::post('delete-event', 'EventDesignController@delete_event');

// Dashboard statistics & scanning
Router::get('dashboard-overview', 'AdminController@overview');
Router::post('run-full-scan', 'AdminController@runFullScan');
Router::get('last-report', 'AdminController@lastReport');
