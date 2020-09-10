<?php

namespace Auth\Handler;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Nowakowskir\JWT\TokenDecoded;
use Auth\Service\{Key, KeyAware, Oauth, OauthAware, TokenAware, Token, User, UserAware};
use Auth\Model\Payload;

class Authenticate implements RequestHandlerInterface, OauthAware, UserAware, KeyAware, TokenAware
{
    private Oauth $oAuthService;
    private User $userService;
    private Key $keyService;
    private Token $tokenService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // $domain = $request->getHeader('x-authentication-domain');
        $id = $request->getHeader('x-authentication-id');
        $token = $request->getHeader('x-authentication-token');

        $response = $this->oAuthService->query($token[0], $id[0]);
        $user = $this->userService->get($response['email']);

        $payload = $user
            ? Payload::fromUser($user)
            : $this->generateUserPayload($response);

        $tokenDecoded = new TokenDecoded(
            [],
            array_merge($payload->jsonSerialize(), ['exp' => time() + 1000])
        );

        $refreshToken = $this->tokenService->build($payload->getEmail());

        return new JsonResponse([
            'token_type' => 'bearer',
                // $payload,
                'token_expiry' => 1000,
                'access_token' => (string) $tokenDecoded->encode($this->keyService->get()),
            ], 200, [
                'Set-Cookie' => "refresh_token={$refreshToken}; HttpOnly; SameSite=Strict; "
                    . 'Expires=' . (new \DateTime())->add(new \DateInterval('P1Y'))->format('D, j M Y H:i:s \G\M\T'),
        ]);
    }

    public function setOauthService(Oauth $service): self
    {
        $this->oAuthService = $service;
        return $this;
    }

    public function setUserService(User $service): self
    {
        $this->userService = $service;
        return $this;
    }

    public function setKeyService(Key $service): self
    {
        $this->keyService = $service;
        return $this;
    }

    public function setTokenService(Token $service): self
    {
        $this->tokenService = $service;
        return $this;
    }

    private function generateUserPayload(array $response): Payload
    {
        $id = $this->userService->create(
            $response['email'],
            $response['first_name'],
            $response['last_name']
        );

        return (new Payload())
            ->setId($id)
            ->setFirstName($response['first_name'])
            ->setLastName($response['last_name'])
            ->setEmail($response['email']);
    }
}
