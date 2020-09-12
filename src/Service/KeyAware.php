<?php

namespace Auth\Service;

interface KeyAware
{
    public function setKeyService(KeyInterface $service): self;
}
