<?php

namespace ModelLogger\Test\Loggers;

use ModelLogger\Logger;
use ModelLogger\Test\Models\Category;
use ModelLogger\Models\Attributes\StringType;

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
                ]
            ],
        ];
    }
}
