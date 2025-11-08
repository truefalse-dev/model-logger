<?php

namespace ModelLogger\Models\Attributes;

class StringType extends BaseType
{
    public const TYPE = 'string';

    public function getValue($value): string|null
    {
        return $value ?? null;
    }
}
