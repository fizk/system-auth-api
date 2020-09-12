<?php

use Auth\Handler\Refresh;
use Auth\Model\Token;
use Auth\Model\User;
use Auth\Service\KeyInterface;
use Auth\Service\RefreshTokenInterface;
use Auth\Service\UserInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class RefreshTest extends TestCase
{
    public function testMissingHeaderValues()
    {
        $this->expectException(InvalidArgumentException::class);
        $request = ServerRequestFactory::fromGlobals([]);
        (new Refresh())->handle($request);
    }

    public function testSuccess()
    {
        $request = (new ServerRequest())
            ->withCookieParams(['refresh_token' => 'refresh_token']);

        $response = (new Refresh())
            ->setRefreshTokenService(new class implements RefreshTokenInterface {
                public function get(string $token): ?Token
                {
                    return (new Token())
                        ->setEmail('some@email.com');
                }

                public function build(string $email): string
                {
                    return '[]';
                }
            })
            ->setUserService(new class implements UserInterface {
                public function get(string $email): ?User
                {
                    return (new User())
                        ->setId('1')
                        ->setFirstName('first')
                        ->setLastName('last')
                        ->setEmail('some@email.com');
                }

                public function create(string $email, string $firstName, string $lastName): ?string
                {
                    return '';
                }
            })
            ->setKeyService(new class implements KeyInterface {
                public function get(): string
                {
                    return '';
                }
            })
        ->handle($request);

        $this->assertEquals(0, strpos('refresh_token=refresh-token;', $response->getHeader('Set-Cookie')[0]));
        $this->assertObjectHasAttribute('token_type', json_decode($response->getBody()));
        $this->assertObjectHasAttribute('token_expiry', json_decode($response->getBody()));
        $this->assertObjectHasAttribute('access_token', json_decode($response->getBody()));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testNoTokenFound()
    {
        $request = (new ServerRequest())
            ->withCookieParams(['refresh_token' => 'refresh_token']);

        $response = (new Refresh())
            ->setRefreshTokenService(new class implements RefreshTokenInterface {
                public function get(string $token): ?Token
                {
                    return null;
                }

                public function build(string $email): string
                {
                    throw new \Exception();
                }
            })
            ->setUserService(new class implements UserInterface {
                public function get(string $email): ?User
                {
                    throw new \Exception();
                }

                public function create(string $email, string $firstName, string $lastName): ?string
                {
                    throw new \Exception();
                }
            })
            ->setKeyService(new class implements KeyInterface {
                public function get(): string
                {
                    throw new \Exception();
                }
            })
        ->handle($request);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertInstanceOf(EmptyResponse::class, $response);
    }
}
