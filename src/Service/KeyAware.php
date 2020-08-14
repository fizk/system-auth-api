<?php

namespace Auth\Service;

interface KeyAware
{
    public function setKeyService(Key $service): self;
}
