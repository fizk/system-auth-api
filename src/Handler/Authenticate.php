<?php

namespace Auth\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Auth\Service\{CredentialsAware, Credentials};
use Zend\Diactoros\Response\EmptyResponse;

class Authenticate implements RequestHandlerInterface, CredentialsAware
{
    private Credentials $credentialsService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $email = $request->getParsedBody()['email'];
        $password = $request->getParsedBody()['password'];
        $response = $this->credentialsService->get($email, $password);

        return $response
            ? new JsonResponse($response, 200)
            : new EmptyResponse(401);
    }

    public function setCredentialsService(Credentials $service): self
    {
        $this->credentialsService = $service;
        return $this;
    }
}
