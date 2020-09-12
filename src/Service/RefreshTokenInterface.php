<?php

namespace Auth\Service;

use Auth\Model\Token;

interface RefreshTokenInterface
{
    public function get(string $token): ?Token;

    public function build(string $email): string;
}
