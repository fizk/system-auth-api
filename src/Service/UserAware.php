<?php

namespace Auth\Service;

interface UserAware
{
    public function setUserService(User $service): self;
}
