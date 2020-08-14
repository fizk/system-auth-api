<?php

namespace Auth\Service;

use MongoDB\Database;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\ServerException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Auth\Service\DatabaseAware;
use Auth\Event\{ServiceError, EventDispatcherAware};
use Auth\Model;

class User implements DatabaseAware, EventDispatcherAware
{
    private Database $client;
    private ?EventDispatcherInterface $eventDispatch = null;

    public function get(string $refresh): ?array
    {
        $document = $this->client->selectCollection('user')->findOne([
            'refresh' => $refresh,
        ]);
        return $document->getArrayCopy();
    }

    public function save(string $token, string $refresh): bool
    {
        try {
            $result = $this->client
                ->selectCollection('user')
                ->insertOne([
                    'token' => $token,
                    'refresh' => $refresh,
                    'created' => new UTCDateTime()
                ]);
            return $result->isAcknowledged();
        } catch (ServerException $e) {
            $this->getEventDispatcher()->dispatch(new ServiceError($e, __METHOD__));
            return false;
        }
    }

    public function setDriver(Database $client): self
    {
        $this->client = $client;
        return $this;
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
