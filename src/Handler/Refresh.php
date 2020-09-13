<?php

namespace Auth\Handler;

use InvalidArgumentException;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\{JsonResponse, EmptyResponse};
use Nowakowskir\JWT\TokenDecoded;
use Auth\Model\Payload;
use Auth\Service\{
    UserAware,
    KeyAware,
    UserInterface,
    KeyInterface,
    RefreshTokenInterface,
    RefreshTokenAware};
use Auth\Model\AuthenticatePayload;

class Refresh implements RequestHandlerInterface, UserAware, KeyAware, RefreshTokenAware
{
    private UserInterface $userService;
    private KeyInterface $keyService;
    private RefreshTokenInterface $refreshTokenService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $cookies = $request->getCookieParams('refresh_token');

        if (!array_key_exists('refresh_token', $cookies)) {
            throw new InvalidArgumentException('Cookie "refresh_token" missing', 400);
        }

        $lastToken = $this->refreshTokenService->get($cookies['refresh_token']);
        if (!$lastToken) {
            return new EmptyResponse(401);
        }

        $expiryTime = 1000;
        $user = $this->userService->get($lastToken->getEmail());
        $payload = Payload::fromUser($user);
        $refreshToken = $this->refreshTokenService->build($payload->getEmail());
        $tokenDecoded = new TokenDecoded(
            [],
            array_merge(
                $payload->jsonSerialize(),
                ['exp' => time() + $expiryTime]
            )
        );

        return new JsonResponse(
            (new AuthenticatePayload())
                ->setExpiry($expiryTime)
                ->setToken((string) $tokenDecoded->encode($this->keyService->get())),
            200,
            [
                'Set-Cookie' => "refresh_token={$refreshToken}; HttpOnly; SameSite=Strict; "
                . 'Expires=' . (new \DateTime())->add(new \DateInterval('P1Y'))->format('D, j M Y H:i:s \G\M\T'),
            ]
        );
    }

    public function setUserService(UserInterface $service): self
    {
        $this->userService = $service;
        return $this;
    }

    public function setKeyService(KeyInterface $service): self
    {
        $this->keyService = $service;
        return $this;
    }

    public function setRefreshTokenService(RefreshTokenInterface $service): self
    {
        $this->refreshTokenService = $service;
        return $this;
    }
}
