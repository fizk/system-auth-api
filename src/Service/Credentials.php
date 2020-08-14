<?php

namespace Auth\Service;

use MongoDB\Database;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\ServerException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Auth\Service\DatabaseAware;
use Auth\Event\{ServiceError, EventDispatcherAware};
use Auth\Model\Authentication;

class Credentials implements DatabaseAware, EventDispatcherAware
{
    private Database $client;
    private ?EventDispatcherInterface $eventDispatch;

    public function get(string $email, string $password): ?Authentication
    {
        $document = $this->client->selectCollection('credentials')->findOne([
            'email' => $email,
            'password' => $password,
        ]);

        return $document
            ? (new Authentication())
                ->setId($document->getArrayCopy()['_id'])
            : null;
    }

    public function save(string $id, string $email, string $password): bool
    {
        try {
            $result = $this->client
                ->selectCollection('credentials')
                ->insertOne([
                    '_id' => $id,
                    'email' => $email,
                    'password' => $password,
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
