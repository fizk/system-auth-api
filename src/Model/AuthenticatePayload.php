<?php

namespace Auth\Model;

use JsonSerializable;

class AuthenticatePayload implements JsonSerializable
{
    private string $tokenType = 'bearer';
    private int $tokenExpiry = 1000;
    private string $accessToken;

    public function setType(string $type): self
    {
        $this->tokenType = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->tokenType;
    }

    public function setExpiry(int $expiry): self
    {
        $this->tokenExpiry = $expiry;
        return $this;
    }

    public function getExpiry(): int
    {
        return $this->tokenExpiry;
    }

    public function setToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getToken(): string
    {
        return $this->accessToken;
    }

    public function jsonSerialize()
    {
        return [
            'token_type' => $this->tokenType,
            'token_expiry' => $this->tokenExpiry,
            'access_token' => $this->accessToken,
        ];
    }
}
