<?php

namespace ModelLogger\Traits;

trait Makeable
{
    public static function make(...$params)
    {
        return new static(...$params);
    }
}