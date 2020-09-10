<?php

return [
    '/' => [
        'GET' => Auth\Handler\Index::class
    ],
    '/authenticate' => [
        'GET' => Auth\Handler\Authenticate::class,
    ],
    '/refresh' => [
        'GET' => Auth\Handler\Refresh::class,
    ],
];
