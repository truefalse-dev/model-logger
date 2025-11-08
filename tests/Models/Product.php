<?php

declare(strict_types=1);

namespace ModelLogger\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';

    protected $guarded = [];

    protected $casts = [
        'vendor_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'float',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->using(CategoryProduct::class);
    }

    public function attribute_values(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value')
            ->using(ProductAttributeValue::class);
    }
}
