<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class ModelLogger extends Model
{
    protected $fillable = [
        'hash',
        'action',
        'logger',
        'model_type',
        'model_id',
        'parent_type',
        'parent_id',
        'changes',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'data' => AsCollection::class,
    ];
}