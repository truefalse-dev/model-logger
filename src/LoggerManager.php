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
            ->map(function (Log $log) {
                return $this->item($log);
            });
    }

    private function query(): Builder
    {
        $query = Log::query()
            ->selectRaw("
                hash,
                JSON_OBJECT(
                    'sections', JSON_OBJECTAGG(id, section),
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
            [$modelType, $modelId] = explode(':', $item->data['model'][$id] ?? '', 2) + [null, null];

            if ($modelId !== null && is_numeric($modelId)) {
                $modelId = (int) $modelId;
            }

            if (empty($parentValue)) {
                $parentType = $modelType;
                $parentId = $modelId;
            } else {
                [$parentType, $parentId] = explode(':', $parentValue, 2) + [null, null];
            }

            $grouped[$id] = [
                'model_type' => $modelType,
                'model_id' => $modelId,
                'section' => $item->data['sections'][$id] ?? null,
                'action' => $item->data['actions'][$id] ?? null,
                'changes' => $item->data['changes'][$id] ?? [],
            ];
        }

        return collect([
//            'parent_type' => $parentType,
//            'parent_id' => (int) $parentId,
            'items' => array_values($grouped),
        ]);
    }
}