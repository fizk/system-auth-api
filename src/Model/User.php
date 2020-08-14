<?php

namespace Auth\Model;

use JsonSerializable;
use DateTime;

class User implements JsonSerializable
{
    private string $id;
    private string $name;
    private DateTime $created;

    public function jsonSerialize()
    {
        return [
            '_id' => $this->id,
            'name' => $this->name,
            'created' => $this->created->format('c'),
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;
        return $this;
    }
}
