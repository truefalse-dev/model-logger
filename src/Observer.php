<?php

namespace ModelLogger;

use Illuminate\Database\Eloquent\Model;
use ModelLogger\Models\Attributes\BaseType;
use ModelLogger\Services\LoggerService;

class Observer
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    protected LoggerService $loggerService;

    public function __construct(LoggerService $loggerService)
    {
        $this->loggerService = $loggerService;
    }

    public function updating(Model $model)
    {
        $this->logChanges($model, self::UPDATE);
    }

    public function created(Model $model)
    {
        $this->logChanges($model, self::CREATE);
    }

    public function deleting(Model $model)
    {
        $this->logChanges($model, self::DELETE);
    }

    protected function logChanges(Model $model, $action)
    {
        $modelClass = get_class($model);

        $logger = $this->loggerService->getLogger($modelClass);

        $loggerConfig = $logger->config();

        if (!array_key_exists($modelClass, $loggerConfig)) {
            return;
        }

        $attributes = $loggerConfig[$modelClass][Logger::ATTRIBUTES];

        $parent = $loggerConfig[$modelClass]['parent'] ?? null;

        $parentClass = null;
        $parentId = null;

        if (method_exists($model, $parent)) {
            $model->loadMissing($parent);

            $parentModel = $model->$parent;

            if ($parentModel) {
                $parentClass = get_class($parentModel);
                $parentId = $parentModel->getKey();
            }
        }

        $changes = [];
        foreach ($attributes as $objAttribute) {

            if (!$objAttribute instanceof BaseType) {
                continue;
            }

            $attribute = $objAttribute->getName();
            $title = $objAttribute->getTitle();

            if (strpos($attribute, '.') !== false) {
                [$relation, $field] = explode('.', $attribute, 2);

                $relatedModel = $model->$relation;

                if ($relatedModel instanceof Model) {
                    $foreignKey = $model->$relation()->getForeignKeyName();

                    if ($model->isDirty($foreignKey) || $action === self::DELETE) {

                        $originalKey = $model->getOriginal($foreignKey);
                        /** @var Model $relatedModelClass */
                        $relatedModelClass = get_class($relatedModel);

                        $changes[$attribute] = [
                            'title' => $title,
                            'old' => $objAttribute->getValue($relatedModelClass::find($originalKey)?->$field),
                            'new' => $objAttribute->getValue($relatedModel->$field),
                        ];;
                    }
                }
            } else {
                if ($model->isDirty($attribute) || $action === self::DELETE) {
                    $changes[$attribute] = [
                        'title' => $title,
                        'old' => $objAttribute->getValue($model->getOriginal($attribute)),
                        'new' => $objAttribute->getValue($model->$attribute),
                    ];
                }
            }
        }

        if ($action === self::DELETE) {
            foreach ($changes as &$change) {
                if (isset($change['new'])) {
                    $change['new'] = null;
                }
            }
            unset($change);
        }

        if (empty($changes)) {
            return;
        }

        $section = $loggerConfig[$modelClass][Logger::SECTION] ?? null;

        $this->loggerService->saveLog([
            'model' => $model,
            'action' => $action,
            'logger' => $logger->getLoggerName(),
            'section' => $section,
            'model_type' => $modelClass,
            'parent_type' => $parentClass,
            'parent_id' => $parentId,
            'changes' => $changes,
        ]);
    }
}