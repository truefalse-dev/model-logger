<?php

namespace ModelLogger;

abstract class Logger
{
    abstract public function config(): array;

    public function getLoggerName(): string
    {
        return static::$loggerName;
    }
}