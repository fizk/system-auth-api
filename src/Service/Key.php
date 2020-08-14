<?php

namespace Auth\Service;

use MongoDB\Database;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\ServerException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Auth\Service\DatabaseAware;
use Auth\Event\{ServiceError, EventDispatcherAware};
use Auth\Model;

class Key implements EventDispatcherAware
{

    private ?EventDispatcherInterface $eventDispatch;

    public function get(): string
    {
        return 'fhgw96rbvzld5y747fhbvpq57bhnsdkgj';
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatch ?: new class implements EventDispatcherInterface {
            public function dispatch(object $event)
            {
            }
        };
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatch): self
    {
        $this->eventDispatch = $eventDispatch;
        return $this;
    }
}
