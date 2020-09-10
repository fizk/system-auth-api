<?php

namespace Auth\Service;

interface Oauth
{
    public function query(string $token, string $id);
}
