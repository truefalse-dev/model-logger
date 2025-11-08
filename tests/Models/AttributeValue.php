<?php

declare(strict_types=1);

namespace ModelLogger\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValue extends Model
{
    protected $guarded = [];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
