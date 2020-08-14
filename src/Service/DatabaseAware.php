<?php

namespace Auth\Service;

use MongoDB\Database;

interface DatabaseAware
{
    public function setDriver(Database $client): self;
}
