<?php

namespace Auth\Service;

use Auth\Model\OAuthResponse;

interface OAuthInterface
{
    public function query(string $token, string $id, string $domain): OAuthResponse;
}
