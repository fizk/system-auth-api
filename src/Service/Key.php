<?php

namespace Auth\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Auth\Event\{EventDispatcherAware};
use Auth\Service\ServiceEventTrait;


class Key implements EventDispatcherAware
{
    use ServiceEventTrait;

    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function get(): string
    {
        return $this->key;
    }
}
