<?php

return [
    /**
     * Access to use the Model Logger feature
     */
    'enabled' => env('MODEL_LOGGER_ENABLED', true),

    /**
     * Model Logger table name
     */
    'table_name' => env('MODEL_LOGGER_TABLE_NAME', 'model_logs'),

    /**
     * The list of active Loggers
     */
    'loggers' => [
       // ...
    ]
];
