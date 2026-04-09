<?php


use SearchTracker\Rus\Http\Router\Router;

Router::get('settings', 'AdminController@getSettings');
Router::post('settings', 'AdminController@saveSettings');
Router::get('get-data', 'AdminController@index');
Router::post('delete-search-terms', 'AdminController@deleteData');
Router::post('delete-search-terms-all', 'AdminController@deleteAllData');
Router::post('export-search-terms', 'AdminController@exportData');
Router::post('export-search-terms-selected', 'AdminController@exportSelectedData');
