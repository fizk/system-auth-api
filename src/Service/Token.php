<?php

namespace Auth\Service;

use MongoDB\BSON\UTCDateTime;
use Auth\Event\{EventDispatcherAware};
use Auth\Service\{ServiceEventTrait, DatabaseAware, ServiceDatabaseTrait};

class Token implements EventDispatcherAware, DatabaseAware
{
    use ServiceEventTrait;
    use ServiceDatabaseTrait;

    public function get(string $token): ?array
    {

        $document = $this->getDriver()->selectCollection('token')->findOne([
            'token' => $token
        ]);

        return $document
            ? $document->getArrayCopy()
            : null;
    }

    public function build(string $email): string
    {
        $token = md5($email . time());
        $this->getDriver()->selectCollection('token')->findOneAndUpdate(
            ['email' => $email],
            ['$set' => ['email' => $email,'token' => $token, 'created' => new UTCDateTime()]],
            ['$upsert' => true]
        );

        return $token;
    }
}
