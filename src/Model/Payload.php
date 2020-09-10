<?php

namespace Auth\Model;

use JsonSerializable;
use Auth\Model\User;

class Payload implements JsonSerializable
{
    private string $id;
    private string $firstName;
    private string $lastName;
    private string $email;

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
        ];
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
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

    public static function fromUser(User $user): Payload
    {
        return (new Payload)
            ->setId($user->getId())
            ->setFirstName($user->getFirstName())
            ->setLastName($user->getLastName())
            ->setEmail($user->getEmail());
    }
}
