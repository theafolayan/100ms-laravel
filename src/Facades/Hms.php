<?php

namespace TheAfolayan\HmsLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class Hms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return '100ms';
    }
}
