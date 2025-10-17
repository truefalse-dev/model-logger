<?php

namespace ModelLogger\Services;

use ModelLogger\Traits\Makeable;

class LoggerService
{
    use Makeable;
    private array $models = [];

    public function __construct(array $loggers)
    {
        foreach($loggers as $loggerClass) {
            $logger = app($loggerClass);
            foreach (array_keys($logger->config()) as $modelClass) {
                $this->models[$modelClass] = $logger;
            }
        }
    }

    public function getLogger($modelClass)
    {
        return $this->models[$modelClass] ?? null;
    }
}
