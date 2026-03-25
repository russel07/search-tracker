<?php

use SearchTracker\Rus\Hooks\Handler\ActionHooksHandler;


//Hooks to check mail need to send or not
add_action('template_redirect', [ActionHooksHandler::class, 'get_search_terms_and_keywords']);
add_action('wp', [ActionHooksHandler::class, 'get_search_terms_and_courses']);