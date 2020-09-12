<?php

namespace Auth\Service;

use MongoDB\BSON\UTCDateTime;
use Auth\Event\{EventDispatcherAware};
use Auth\Model\Token;
use Auth\Service\{ServiceEventTrait, DatabaseAware, ServiceDatabaseTrait};

class RefreshToken implements RefreshTokenInterface, EventDispatcherAware, DatabaseAware
{
    use ServiceEventTrait;
    use ServiceDatabaseTrait;

    public function get(string $token): ?Token
    {
        $document = $this->getDriver()->selectCollection('token')->findOne([
            'token' => $token
        ]);

        return $document
            ? (new Token())
                ->setEmail($document->getArrayCopy()['email'])
                ->setToken($document->getArrayCopy()['token'])
                ->setCreated($document->getArrayCopy()['created']->toDateTime())
            : null;
    }

    public function build(string $email): string
    {
        $token = md5($email . time());
        $this->getDriver()->selectCollection('token')->updateOne(
            ['email' => $email],
            ['$set' => ['email' => $email,'token' => $token, 'created' => new UTCDateTime()]],
            ['upsert' => true]
        );

        return $token;
    }
}
