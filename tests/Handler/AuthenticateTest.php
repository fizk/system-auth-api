<?php

use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Response\{JsonResponse, EmptyResponse};
use Laminas\Diactoros\ServerRequestFactory;
use Auth\Handler\Authenticate;
use Auth\Service\Credentials;
use Auth\Model\Authentication;

class AuthenticateTest extends TestCase {
    public function testFail() {
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '', [])
            ->withParsedBody([
                'email' => 'email',
                'password' => 'password'
            ]);

        /** @var $mock \Mockery\MockInterface|\Mockery\LegacyMockInterface */
        $mock = \Mockery::mock(Credentials::class);
        $mock->shouldReceive('get')
            ->andReturn(null)
            ->mock();

        /** @var $response EmptyResponse */
        $response = (new Authenticate())
            ->setCredentialsService($mock)
            ->handle($request);

        $this->assertInstanceOf(EmptyResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testPass()
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '', [])
            ->withParsedBody([
                'email' => 'email',
                'password' => 'password'
            ]);

        $model = (new Authentication)
                ->setId('1');

        /** @var $mock \Mockery\MockInterface|\Mockery\LegacyMockInterface */
        $mock = \Mockery::mock(Credentials::class);
        $mock->shouldReceive('get')
        ->andReturn($model)
            ->mock();

        /** @var $response JsonResponse */
        $response = (new Authenticate())
            ->setCredentialsService($mock)
            ->handle($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($model, $response->getPayload());
    }
}
