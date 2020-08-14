<?php

namespace Auth\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Auth\Service\{CredentialsAware, Credentials};
use Zend\Diactoros\Response\EmptyResponse;

class Create implements RequestHandlerInterface, CredentialsAware
{
    private Credentials $credentialsService;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('user_id');
        $email = $request->getParsedBody()['email'];
        $password = $request->getParsedBody()['password'];
        $response = $this->credentialsService->save($id, $email, $password);

        return $response
            ? new EmptyResponse(201)
            : new EmptyResponse(400);
    }

    public function setCredentialsService(Credentials $service): self
    {
        $this->credentialsService = $service;
        return $this;
    }
}
