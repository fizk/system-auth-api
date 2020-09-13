<?php

namespace Auth\Handler;

use InvalidArgumentException;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Nowakowskir\JWT\TokenDecoded;
use Auth\Model\{Payload, AuthenticatePayload, OAuthResponse};
use Auth\Service\{
    OAuthAware,
    UserAware,
    KeyAware,
    RefreshTokenAware,
    OAuthInterface,
    UserInterface,
    KeyInterface,
    RefreshTokenInterface
};

class Authenticate implements RequestHandlerInterface, OAuthAware, UserAware, KeyAware, RefreshTokenAware
{
    private OAuthInterface $oAuthService;
    private UserInterface $userService;
    private KeyInterface $keyService;
    private RefreshTokenInterface $refreshTokenService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $domain = $request->getHeader('x-authentication-domain');
        $id = $request->getHeader('x-authentication-id');
        $token = $request->getHeader('x-authentication-token');

        if (count($domain) === 0 || count($id) === 0 || count($token) === 0) {
            throw new InvalidArgumentException('Header values missing', 400);
        }

        $expiryTime = 1000;
        $authUser = $this->oAuthService->query($token[0], $id[0], $domain[0]);
        $user = $this->userService->get($authUser->getLastName());
        $payload = $user
            ? Payload::fromUser($user)
            : $this->createUser($authUser);
        $refreshToken = $this->refreshTokenService->build($payload->getEmail());
        $tokenDecoded = new TokenDecoded(
            [],
            array_merge($payload->jsonSerialize(), ['exp' => time() + $expiryTime])
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

    public function setOAuthService(OAuthInterface $service): self
    {
        $this->oAuthService = $service;
        return $this;
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

    private function createUser(OAuthResponse $response): Payload
    {
        $id = $this->userService->create(
            $response->getEmail(),
            $response->getFirstName(),
            $response->getLastName()
        );

        return (new Payload())
            ->setId($id)
            ->setFirstName($response->getFirstName())
            ->setLastName($response->getLastName())
            ->setEmail($response->getEmail());
    }
}
