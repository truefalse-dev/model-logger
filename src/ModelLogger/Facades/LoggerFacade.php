<?php

namespace ModelLogger\Facades;

use Illuminate\Support\Facades\Facade;

class LoggerFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'model-logger';
    }
}