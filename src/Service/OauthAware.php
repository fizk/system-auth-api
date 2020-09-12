<?php

namespace Auth\Service;

interface OAuthAware
{
    public function setOAuthService(OAuthInterface $service): self;
}
