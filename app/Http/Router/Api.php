<?php


use SearchTracker\Rus\Http\Router\Router;

Router::get('get-data', 'AdminController@index');
Router::post('delete-search-terms', 'AdminController@deleteData');
