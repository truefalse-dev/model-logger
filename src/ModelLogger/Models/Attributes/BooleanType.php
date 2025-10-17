<?php

namespace ModelLogger\Attributes;

use ModelLogger\Attributes\BaseType;

class BooleanType extends BaseType
{
    public const TYPE = 'boolean';

    public function getType(): string
    {
        return static::TYPE;
    }

    public function getValue($value): string|null
    {
        return $value ? 'On' : 'Off';
    }
}