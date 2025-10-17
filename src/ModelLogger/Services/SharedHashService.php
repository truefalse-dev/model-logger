<?php

namespace ModelLogger\Services;

class SharedHashService
{
    protected ?string $sharedHash = null;

    public function getSharedHash(): string
    {
        return $this->sharedHash;
    }

    public function setSharedHash(string $hash): self
    {
        $this->sharedHash = $hash;
        return $this;
    }
}