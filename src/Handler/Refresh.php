<?php

namespace Auth\Handler;

use Auth\Model\Payload;
use Laminas\Diactoros\Response\{JsonResponse, EmptyResponse};
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Auth\Service\{UserAware, KeyAware, User, Key, Token, TokenAware};
use Nowakowskir\JWT\TokenDecoded;

class Refresh implements RequestHandlerInterface, UserAware, KeyAware, TokenAware
{
    private User $userService;
    private Key $keyService;
    private Token $tokenService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $cookies = $request->getCookieParams('refresh_token');
        $response = $this->tokenService->get($cookies['refresh_token']);

        if (!$response) {
            return new EmptyResponse(401);
        }

        $user = $this->userService->get($response['email']);
        $payload = Payload::fromUser($user);
        $refreshToken = $this->tokenService->build($payload->getEmail());
        $tokenDecoded = new TokenDecoded(
            [],
            array_merge($payload->jsonSerialize(), ['exp' => time() + 1000])
        );

        return new JsonResponse([
            'token_type' => 'bearer',
            'token_expiry' => 1000,
            'access_token' => (string) $tokenDecoded->encode($this->keyService->get()),
        ], 200, [
            'Set-Cookie' => "refresh_token={$refreshToken}; HttpOnly; SameSite=Strict; "
            . 'Expires=' . (new \DateTime())->add(new \DateInterval('P1Y'))->format('D, j M Y H:i:s \G\M\T'),
        ]);
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
}
