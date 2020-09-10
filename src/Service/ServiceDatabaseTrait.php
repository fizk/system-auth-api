<?php

namespace Auth\Service;

use MongoDB\Database;

trait ServiceDatabaseTrait
{
    private Database $driver;

    public function setDriver(Database $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    public function getDriver(): Database
    {
        return $this->driver;
    }
}
