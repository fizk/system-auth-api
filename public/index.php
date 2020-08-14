<?php

chdir(dirname(__DIR__));
require_once './vendor/autoload.php';

use Highway\{Route, Router};
use Psr\EventDispatcher\EventDispatcherInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\ServiceManager\ServiceManager;
use Auth\Event\SystemError;
use Auth\Handler;


// PUT data workaround
//  for some reason PHP doesn't have $_PUT; and $_POST doesn't contain PUT body
$putdata = fopen("php://input", "r");
$string = '';
while ($data = fread($putdata, 1024)) $string .= $data;
fclose($putdata);
mb_parse_str($string, $result);


// Create an instance of PSR-7 ServerRequestInterface object
// using Zend\Diactoros
$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $result, //$_POST,
    $_COOKIE,
    $_FILES
);

// Create a new instance of Highway\Router

$serviceManager = new ServiceManager(require_once './config/service.php');
$router = new Router();
$emitter = new SapiEmitter();
try {
    $router->get("/", $serviceManager->get(Handler\Index::class));
    $router->post("/authenticate", $serviceManager->get(Handler\Authenticate::class));
    $router->put("/create/{user_id}", $serviceManager->get(Handler\Create::class));
    $router->post("/login", $serviceManager->get(Handler\Login::class));
    $router->get("/refresh/{token}", $serviceManager->get(Handler\Refresh::class));

    $emitter->emit($router->match($request)->handle($request));
} catch (\Throwable $e) {
    $serviceManager->get(EventDispatcherInterface::class)
        ->dispatch(new SystemError($e, 'SYSTEM'));
    $emitter->emit(new JsonResponse([
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'path' => "{$e->getFile()}:{$e->getLine()}",
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ],500)
    );
}
