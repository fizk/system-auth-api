<?php

namespace Auth\Service;

use Auth\Service\DatabaseAware;
use Auth\Model\User as UserModel;
use Auth\Event\EventDispatcherAware;
use Auth\Service\ServiceDatabaseTrait;
use Auth\Service\ServiceEventTrait;

class User implements DatabaseAware, EventDispatcherAware
{
    use ServiceDatabaseTrait;
    use ServiceEventTrait;

    public function get(string $email): ?UserModel
    {
        $document = $this->getDriver()->selectCollection('user')->findOne([
            'email' => $email,
        ]);
        return $document
            ? (new UserModel)
                ->setId((string) $document->getArrayCopy()['_id'])
                ->setFirstName((string) $document->getArrayCopy()['first_name'])
                ->setLastName((string) $document->getArrayCopy()['last_name'])
                ->setEmail((string) $document->getArrayCopy()['email'])
            : null ;
    }

    public function create(string $email, string $firstName, string $lastName): ?string
    {
        $response = $this->getDriver()->selectCollection('user')->insertOne([
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
        return $response->isAcknowledged()
            ? $response->getInsertedId()
            : null;
    }
}
