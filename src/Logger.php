<?php

namespace ModelLogger;

abstract class Logger
{
    public const SECTION = 'section';
    public const ATTRIBUTES = 'attributes';
    public const PARENT = 'parent';

    abstract public function config(): array;

    public function getLoggerName(): string
    {
        return static::$loggerName;
    }
}