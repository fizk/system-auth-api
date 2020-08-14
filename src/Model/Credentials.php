<?php

namespace Auth\Model;

use JsonSerializable;
use DateTime;

class Credentials implements JsonSerializable
{
    private string $id;
    private string $email;
    private string $password;
    private DateTime $created;

    public function jsonSerialize()
    {
        return [
            '_id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->passwors;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
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
