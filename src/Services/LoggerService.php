<?php

namespace ModelLogger\Services;

use ModelLogger\Models\Log;

class LoggerService
{
    private array $models = [];
    private SharedHashService $sharedHashService;

    public function __construct(array $loggers, SharedHashService $sharedHashService)
    {
        $this->sharedHashService = $sharedHashService;
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

    public function saveLog(array $data): Log
    {
        return Log::create([
            'hash' => $this->sharedHashService->getSharedHash(),
            'action' => $data['action'],
            'section' => $data['section'],
            'logger' => $data['logger'],
            'model_type' => $data['model_type'],
            'model_id' => $data['model']->getKey(),
            'parent_type' => $data['parent_type'],
            'parent_id' => $data['parent_id'],
            'changes' => $data['changes'],
        ]);
    }
}
