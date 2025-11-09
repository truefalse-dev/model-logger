<?php

namespace ModelLogger\Test\Loggers;

use ModelLogger\Logger;
use ModelLogger\Models\Attributes\StringType;
use ModelLogger\Models\Attributes\NumberType;
use ModelLogger\Models\Attributes\BooleanType;

// Models
use ModelLogger\Test\Models\Product;
use ModelLogger\Test\Models\CategoryProduct;
use ModelLogger\Test\Models\ProductAttributeValue;
use ModelLogger\Test\Models\ProductReview;

class ProductReviewLogger extends Logger
{
    protected static string $loggerName = 'Review';

    public function config(): array
    {
        return [
            ProductReview::class => [
                self::PARENT => 'product',
                self::SECTION => 'Review',
                self::ATTRIBUTES => [
                    StringType::make('description'),
                ]
            ]
        ];
    }
}
