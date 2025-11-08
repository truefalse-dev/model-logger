<?php

namespace ModelLogger\Services;

use Illuminate\Database\Eloquent\Model;

class SessionService
{
    protected ?string $hash = null;
    protected ?Model $user;

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return $this
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getUserId(): int|null
    {
        return $this->user?->id;
    }

    /**
     * @param Model|null $user
     * @return $this
     */
    public function setUser(?Model $user): self
    {
        $this->user = $user;
        return $this;
    }
}
