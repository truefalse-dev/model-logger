<?php

namespace ModelLogger\Test\Loggers;

use ModelLogger\Logger;
use ModelLogger\Test\Models\CategoryProduct;
use ModelLogger\Test\Models\Product;
use ModelLogger\Models\Attributes\StringType;

class ProductLogger extends Logger
{
    protected static string $loggerName = 'Product';

    public function config(): array
    {
        return [
            Product::class => [
                self::SECTION => 'Product',
                self::ATTRIBUTES => [
                    StringType::make('name'),
                ]
            ],
            CategoryProduct::class => [
                'parent' => 'product',
                self::SECTION => 'Category',
                self::ATTRIBUTES => [
                    StringType::make('category.name', 'Name'),
                ]
            ],
        ];
    }
}
