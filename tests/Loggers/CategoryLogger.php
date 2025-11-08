<?php

namespace ModelLogger\Test\Loggers;

use ModelLogger\Logger;
use ModelLogger\Test\Models\Category;
use ModelLogger\Models\Attributes\StringType;
use ModelLogger\Models\Attributes\BooleanType;

class CategoryLogger extends Logger
{
    protected static string $loggerName = 'Category';

    public function config(): array
    {
        return [
            Category::class => [
                self::SECTION => 'Category',
                self::ATTRIBUTES => [
                    StringType::make('name'),
                    BooleanType::make('status'),
                ]
            ],
        ];
    }
}
