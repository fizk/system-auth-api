<?php

use Auth\Handler\Authenticate;
use Auth\Model\OAuthResponse;
use Auth\Model\Token;
use Auth\Model\User;
use Auth\Service\KeyInterface;
use Auth\Service\OAuthInterface;
use Auth\Service\RefreshTokenInterface;
use Auth\Service\UserInterface;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Response\{JsonResponse};
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;

class AuthenticateTest extends TestCase {

    public function testMissingHeaderValues()
    {
        $request = ServerRequestFactory::fromGlobals([]);
        $this->expectException(InvalidArgumentException::class);
        (new Authenticate())->handle($request);
    }

    public function testGenerateUser()
    {
        $request = (new ServerRequest())
            ->withHeader('x-authentication-domain', 'domain')
            ->withHeader('x-authentication-id', 'id')
            ->withHeader('x-authentication-token', 'token')
            ;

        $response = (new Authenticate())
            ->setKeyService(new class implements KeyInterface {
                public function get(): string {
                    return '';
                }
            })
            ->setOauthService(new class implements OAuthInterface {
                public function query(string $token, string $id, string $domain): OAuthResponse
                {
                    return (new OAuthResponse())
                        ->setEmail('some@email.com')
                        ->setFirstName('first')
                        ->setLastName('last')
                        ->setId('1');
                }
            })
            ->setRefreshTokenService(new class implements RefreshTokenInterface {
                public function get(string $token): ?Token
                {
                    return new Token();
                }

                public function build(string $email): string
                {
                    return 'refresh-token';
                }
            })
            ->setUserService(new class implements UserInterface {
                public function get(string $email): ?User
                {
                    return null;
                }

                public function create(string $email, string $firstName, string $lastName): ?string
                {
                    return 'auto-generated-user-id';
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

    public function testUserExist()
    {
        $request = (new ServerRequest())
            ->withHeader('x-authentication-domain', 'domain')
            ->withHeader('x-authentication-id', 'id')
            ->withHeader('x-authentication-token', 'token');

        $response = (new Authenticate())
            ->setKeyService(new class implements KeyInterface
            {
                public function get(): string
                {
                    return '';
                }
            })
            ->setOauthService(new class implements OAuthInterface
            {
                public function query(string $token, string $id, string $domain): OAuthResponse
                {
                    return (new OAuthResponse())
                        ->setEmail('some@email.com')
                        ->setFirstName('first')
                        ->setLastName('last')
                        ->setId('1');
                }
            })
            ->setRefreshTokenService(new class implements RefreshTokenInterface
            {
                public function get(string $token): ?Token
                {
                    return new Token();
                }

                public function build(string $email): string
                {
                    return 'refresh-token';
                }
            })
            ->setUserService(new class implements UserInterface
            {
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
                    throw new \Exception();
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
}
