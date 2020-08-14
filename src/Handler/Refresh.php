<?php

namespace Auth\Handler;

use Laminas\Diactoros\Response\{JsonResponse, EmptyResponse};
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Auth\Service\{UserAware, KeyAware, User, Key};
use Nowakowskir\JWT\{TokenDecoded, TokenEncoded};
use function json_decode;
use function uniqid;

class Refresh implements RequestHandlerInterface, UserAware, KeyAware
{
    private User $userService;
    private Key $keyService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $refreshToken = $request->getAttribute('token');
        $response = $this->userService->get($refreshToken);

        if (! $response) {
            return new EmptyResponse(401);
        }

        $payload = (new TokenEncoded($response['token']))->decode()->getPayload();

        $tokenDecoded = new TokenDecoded(
            [],
            array_merge($payload, ['exp' => time() + 1000])
        );
        $tokenEncoded = $tokenDecoded->encode($this->keyService->get());
        $refreshToken = md5(uniqid());
        $this->userService->save($tokenEncoded, $refreshToken);

        return new JsonResponse([
            'token' => (string)$tokenEncoded,
            'refresh' => $refreshToken,
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
}
