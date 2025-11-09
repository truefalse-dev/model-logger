<?php

namespace ModelLogger\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $guarded = [];
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
