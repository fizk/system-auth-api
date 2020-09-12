<?php

namespace Auth\Service;

use Auth\Model\User;

interface UserInterface
{
    public function get(string $email): ?User;

    public function create(string $email, string $firstName, string $lastName): ?string;
}
