<?php


use SearchTracker\Rus\Http\Router\Router;

Router::get('get-data', 'AdminController@index');
Router::post('delete-search-terms', 'AdminController@deleteData');
Router::post('export-search-terms', 'AdminController@exportData');
Router::post('export-search-terms-selected', 'AdminController@exportSelectedData');
