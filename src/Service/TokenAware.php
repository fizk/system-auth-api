<?php

namespace Auth\Service;

interface TokenAware
{
    public function setTokenService(Token $service): self;
}
