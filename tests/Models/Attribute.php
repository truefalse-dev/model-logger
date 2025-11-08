<?php

declare(strict_types=1);

namespace ModelLogger\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attribute extends Model
{
    protected $table = 'attributes';

    protected $guarded = [];
}
