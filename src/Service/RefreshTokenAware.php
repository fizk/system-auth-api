<?php

namespace Auth\Service;

interface RefreshTokenAware
{
    public function setRefreshTokenService(RefreshTokenInterface $service): self;
}
