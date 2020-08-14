<?php

namespace Auth\Service;

use MongoDB\Database;
use Auth\Service\Credentials;

interface CredentialsAware
{
    public function setCredentialsService(Credentials $service): self;
}
