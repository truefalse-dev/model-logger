<?php

namespace ModelLogger\Test\Loggers;

use ModelLogger\Logger;
use ModelLogger\Models\Attributes\StringType;
use ModelLogger\Models\Attributes\NumberType;

// Models
use ModelLogger\Test\Models\Product;
use ModelLogger\Test\Models\CategoryProduct;
use ModelLogger\Test\Models\ProductAttributeValue;

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
                    StringType::make('vendor.name'),
                    NumberType::make('price'),
                    NumberType::make('quantity'),
                ]
            ],
            CategoryProduct::class => [
                self::PARENT => 'product',
                self::SECTION => 'Category',
                self::ATTRIBUTES => [
                    StringType::make('category.name', 'Name'),
                ]
            ],
            ProductAttributeValue::class => [
                self::PARENT => 'product',
                self::SECTION => 'Attribute value',
                self::ATTRIBUTES => [
                    StringType::make('attribute_value.name')->markAs('attribute_value.attribute.name'),
                ]
            ]
        ];
    }
}
