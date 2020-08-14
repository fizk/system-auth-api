<?php

namespace Auth\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

class Index implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'status' => 'ok',
            'endpoints' => [
                '/authenticate' => [
                    'params' => [],
                    'post' => [
                        'request' => [
                            'email' => '@string',
                            'password' => '@string',
                        ],
                        'response' => [
                            200 => '@Authentication',
                            401 => 'error'
                        ],
                    ]
                ],
                '/create/{user_id}' => [
                    'params' => [
                        'user_id' => '@string'
                    ],
                    'put' => [
                        'request' => [
                            'email' => '@string',
                            'password' => '@string',
                        ],
                        'response' => [
                            201 => 'user created',
                            400 => 'error',
                         ]
                    ]
                ],
                '/login' => [
                    'params' => [],
                    'post' => [
                        'request' => [
                            'payload' => '@json'
                        ],
                        'response' => [
                            200 => '@AuthTokens',
                        ],
                    ],
                ],
            ],
            'models' => [
                'Authentication' => [
                    '_id' => '@string'
                ],
                'AuthTokens' => [
                    'token' => '@string | JWT',
                    'refresh' => '@string'
                ]
            ]
        ]);
    }
}
