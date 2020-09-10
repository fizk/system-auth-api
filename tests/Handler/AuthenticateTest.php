<?php

use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Response\{JsonResponse, EmptyResponse};
use Laminas\Diactoros\ServerRequestFactory;
use Auth\Handler\Authenticate;
use Auth\Service\Credentials;
use Auth\Model\Authentication;

class AuthenticateTest extends TestCase {
    public function testFail() {
        $this->assertTrue(true);
    }
}
