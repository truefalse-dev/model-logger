<?php

namespace ModelLogger\Services;

class SessionService
{
    protected ?string $hash = null;

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }
}