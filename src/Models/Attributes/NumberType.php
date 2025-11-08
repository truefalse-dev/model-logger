<?php

namespace ModelLogger\Models\Attributes;

class NumberType extends BaseType
{
    public const TYPE = 'number';

    public function getValue($value): float|int|null
    {
        return (intval($value) == floatval($value)
            ? (int) $value
            : (float) $value) ?? null;
    }
}
