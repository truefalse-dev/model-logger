<?php

namespace ModelLogger\Attributes;

use ModelLogger\Attributes\BaseType;

class StringType extends BaseType
{
    public const TYPE = 'string';

    public function getType(): string
    {
        return static::TYPE;
    }

    public function getValue($value): string|null
    {
        return $value ?? null;
    }
}