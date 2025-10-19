<?php

use ModelLogger\LoggerManager;

if (!function_exists('modelLog')) {
    function modelLog()
    {
        return new LoggerManager;
    }
}