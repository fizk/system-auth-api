<?php

use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Response\{JsonResponse, EmptyResponse};
use Laminas\Diactoros\ServerRequestFactory;
use Auth\Handler\Create;
use Auth\Service\Credentials;
use Auth\Model\Authentication;

class CreateTest extends TestCase {
    public function testPass() {
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '', [])
            ->withAttribute('user_id', 'user-id')
            ->withParsedBody([
                'email' => 'email',
                'password' => 'password'
            ]);

        /** @var $mock \Mockery\MockInterface|\Mockery\LegacyMockInterface */
        $mock = \Mockery::mock(Credentials::class);
        $mock->shouldReceive('save')
            ->andReturn(true)
            ->mock();

        /** @var $response EmptyResponse */
        $response = (new Create())
            ->setCredentialsService($mock)
            ->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testFail()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('PUT', '', [])
            ->withAttribute('user_id', 'user-id')
            ->withParsedBody([
                'email' => 'email',
                'password' => 'password'
            ]);

        /** @var $mock \Mockery\MockInterface|\Mockery\LegacyMockInterface */
        $mock = \Mockery::mock(Credentials::class);
        $mock->shouldReceive('save')
            ->andReturn(false)
            ->mock();

        /** @var $response EmptyResponse */
        $response = (new Create())
            ->setCredentialsService($mock)
            ->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }
}
