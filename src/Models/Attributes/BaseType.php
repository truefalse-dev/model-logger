<?php

namespace ModelLogger\Models\Attributes;

use Illuminate\Support\Str;
use ModelLogger\Traits\Makeable;

/**
 * @method static make(string $name, string $name)
 */
class BaseType
{
    use Makeable;

    protected $name;
    protected $title;

    public function __construct(string $name, string $title = null) {
        $this->name = $name;
        $this->title = $title ?? Str::ucfirst(str_replace('.', ' ', $name));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}