<?php

namespace ModelLogger\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class Log extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'model_logs';

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