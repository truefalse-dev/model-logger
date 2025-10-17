<?php

namespace ModelLogger\Models\Attributes;

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