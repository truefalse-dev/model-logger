<?php

use ModelLogger\LoggerManager;

if (!function_exists('modelLogger')) {
    function modelLogger()
    {
        return new LoggerManager;
    }
}