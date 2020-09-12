<?php

namespace Auth\Service;

interface UserAware
{
    public function setUserService(UserInterface $service): self;
}
