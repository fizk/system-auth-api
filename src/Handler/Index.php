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
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Auth API',
                'description' => 'The Auth API',
                'version' => '1.0.0'
            ],
            'servers' => [
                [
                    'url' => 'http://localhost:8081',
                    'description' => 'URL description'
                ]
            ],
            'paths' => [
                '/authenticate' => [
                    'get' => [
                        'summary' => 'Authenticate user',
                        'description' => 'Authenticate user with OAuth token (and additional data)',
                        'parameters' => [
                            [
                                'name' => 'x-authentication-domain',
                                'in' => 'header',
                                'description' => 'Name of service to use: facebook | google',
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                            [
                                'name' => 'x-authentication-id',
                                'in' => 'header',
                                'description' => 'User ID (or null)',
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                            [
                                'name' => 'x-authentication-token',
                                'in' => 'header',
                                'description' => 'OAuth token',
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'Returns AuthenticatePayload which contains a JWT token.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/AuthenticatePayload',
                                        ]
                                    ]
                                ]
                            ],
                            401 => [
                                'description' => 'Can\'t authenticate',
                            ]
                        ]
                    ],
                ],
                '/refresh' => [
                    'get' => [
                        'summary' => 'Refreshes user "session"',
                        'description' => 'Accepts Cookie to refresh user session',
                        'parameters' => [
                            [
                                'name' => 'refresh_token',
                                'in' => 'cookie',
                                'description' => 'Refresh Token',
                                'schema' => [
                                    'type' => 'string'
                                ]
                            ],
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'Returns AuthenticatePayload which contains a JWT token.',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/AuthenticatePayload',
                                        ]
                                    ]
                                ]
                            ],
                            401 => [
                                'description' => 'Can\'t authenticate',
                            ]
                        ]
                    ],
                ],
            ],
            'components' => [
                'schemas' => [
                    'AuthenticatePayload' => [
                        'properties' => [
                            'token_type' => [
                                'type' => 'string'
                            ],
                            'token_expiry' => [
                                'type' => 'integer'
                            ],
                            'access_token' => [
                                'type' => 'string'
                            ],
                        ],
                    ],
                ]
            ]
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }
}
