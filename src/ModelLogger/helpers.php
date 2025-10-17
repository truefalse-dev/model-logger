<?php

use ModelLogger\Facades\LoggerFacade;

if (!function_exists('modelLogger')) {
    function modelLogger()
    {
        return new LoggerFacade;
    }
}