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
    protected $mark;

    public function __construct(string $name, string $title = null) {
        $this->name = $name;
        $this->title = $title ?? Str::ucfirst(str_replace('.', ' ', $name));
    }

    public function getType(): string
    {
        return static::TYPE;
    }

    public function markAs($field): static
    {
        $this->mark = $field;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMark(): string|null
    {
        return $this->mark;
    }
}
