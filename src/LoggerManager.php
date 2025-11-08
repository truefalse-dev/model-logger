<?php

namespace ModelLogger;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use ModelLogger\Models\Log;

class LoggerManager
{
    public const LIMIT = 10;
    public const PAGE = 1;

    private int $limit = self::LIMIT;
    private int $page = self::PAGE;
    private string $whereRaw;

    public function whereRaw(string $whereRaw): static
    {
        $this->whereRaw = $whereRaw;
        return $this;
    }

    public function limit(int $limit = self::LIMIT): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function page(int $page = self::PAGE): static
    {
        $this->page = $page;
        return $this;
    }

    public function get(): Collection
    {
        return $this->query()->get()
            ->mapWithKeys(function (Log $log) {
                return [$log->hash => $this->item($log)];
            });
    }

    private function query(): Builder
    {
        $query = Log::query()
            ->selectRaw("
                hash,
                JSON_OBJECT(
                    'sections', JSON_OBJECTAGG(id, section),
                    'user_id', JSON_OBJECTAGG(id, user_id),
                    'changes', JSON_OBJECTAGG(id, changes),
                    'actions', JSON_OBJECTAGG(id, action),
                    'model', JSON_OBJECTAGG(id, CONCAT(model_type, ':', model_id)),
                    'parent', JSON_OBJECTAGG(
                        id,
                        CASE WHEN parent_type IS NOT NULL THEN CONCAT(parent_type, ':', parent_id) ELSE NULL END
                    )
                ) AS data,
                MIN(created_at) as first_created_at,
                MAX(created_at) as last_created_at
            ");

        if(!empty($this->whereRaw)) {
            $query->whereRaw($this->whereRaw);
        }

        $query->groupBy('hash')
            ->orderByRaw("last_created_at DESC")
            ->limit($this->limit);

        return $query;
    }

    private function item($item): Collection
    {
        $parent = $item->data['parent'] ?? [];

        uasort($parent, function ($a, $b) {
            if ($a === null && $b !== null) {
                return -1;
            }
            if ($a !== null && $b === null) {
                return 1;
            }
            return 0;
        });

        $grouped = [];
        $parentType = null;
        $parentId = null;

        foreach ($parent as $id => $parentValue) {

            $modelHash = $item->data['model'][$id];

            [$modelType, $modelId] = isset($modelHash)
                ? explode(':', $modelHash, 2) + [null, null]
                : null;

            if ($modelId !== null && is_numeric($modelId)) {
                $modelId = (int) $modelId;
            }

            if (empty($parentValue)) {
                $entityType = $modelType;
                $entityId = $modelId;
            } else {
                [$entityType, $entityId] = explode(':', $parentValue, 2) + [null, null];
            }

            $grouped[$id] = [
//                'model_type' => $modelType,
//                'model_id' => $modelId,
                'section' => $item->data['sections'][$id] ?? null,
                'action' => $item->data['actions'][$id] ?? null,
                'changes' => $item->data['changes'][$id] ?? [],
            ];
        }

        return collect([
            'user_id' => $item->data['user_id'][$id] ?? null,
            'entity_type' => $entityType,
            'entity_id' => (int) $entityId,
            'items' => array_values($grouped),
        ]);
    }
}
