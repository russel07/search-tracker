<?php

namespace SearchTracker\Rus\Hooks\Handler;

use SearchTracker\Rus\Foundation\AppHelper;

class FilterHooksHandler
{
    use AppHelper;

    public static function your_filter_hook($value, $arg1)
    {
        // Your filter logic here
        return $value;
    }
}
