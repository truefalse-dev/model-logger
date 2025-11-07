<?php

namespace ModelLogger;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use ModelLogger\Services\LoggerService;
use ModelLogger\Services\SessionService;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(Observer $observer, SessionService $sessionService)
    {
        $this->publishes([
            __DIR__ . '/config/model-logger.php' => config_path('model-logger.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'migrations');

        foreach (config('model-logger.loggers') as $loggerClass) {
            $logger = app($loggerClass);
            foreach (array_keys($logger->config()) as $modelClass) {
                /** @var Model $modelClass */
                $modelClass::observe($observer);
            }
        }

        Event::listen('Illuminate\Routing\Events\RouteMatched', function () use ($observer, $sessionService) {
            $sessionService->setHash(Str::uuid());
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/model-logger.php', 'model-logger'
        );

        $this->app->singleton(SessionService::class);

        $this->app->bind(Observer::class, function ($app) {
            return new Observer(new LoggerService(config('model-logger.loggers'), $app->make(SessionService::class)));
        });
    }
}