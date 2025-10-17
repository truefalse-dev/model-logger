<?php

namespace ModelLogger;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/model-logger.php' => config_path('model-logger.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/model-logger.php', 'model-logger'
        );
    }
}