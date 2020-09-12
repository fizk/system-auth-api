<?php

namespace Auth\Model;

use JsonSerializable;
use DateTime;

class Token implements JsonSerializable
{
    private string $token;
    private string $email;
    private DateTime $created;

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;
        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function jsonSerialize()
    {
        return [
            'token' => $this->token,
            'email' => $this->email,
            'created' => $this->created ? $this->created->format('c') : null,
        ];
    }
}
