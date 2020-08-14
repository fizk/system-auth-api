<?php

namespace Auth\Model;

use JsonSerializable;

class Authentication implements JsonSerializable
{
    private string $id;

    public function jsonSerialize()
    {
        return [
            '_id' => $this->id,
        ];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
}
