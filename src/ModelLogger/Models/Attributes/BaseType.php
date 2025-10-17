<?php

namespace ModelLogger\Models\Attributes;

use ModelLogger\Traits\Makeable;
use Illuminate\Support\Str;

class BaseType
{
    use Makeable;

    protected $name;
    protected $title;

    public function __construct($name, $title = null) {
        $this->name = $name;
        $this->title = $title ?? Str::ucfirst(str_replace('.', ' ', $name));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttribute(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}