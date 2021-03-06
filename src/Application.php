<?php

namespace Auth;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Highway\{Route, RouteCollection};
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Exception;
use Error;
use Auth\Event\SystemError;
use Auth\Response\ErrorJsonResponse;

class Application
{
    private ServiceLocatorInterface $container;
    private EmitterInterface $emitter;
    private array $routes;

    public function __construct(ServiceLocatorInterface $container, EmitterInterface $emitter, array $router)
    {
        $this->container = $container;
        $this->emitter = $emitter;
        $this->routes = $router;
    }

    public function run(ServerRequestInterface $request)
    {
        try {
            $collection = new RouteCollection();
            foreach ($this->routes as $route => $verbs) {
                foreach ($verbs as $verb => $handler) {
                    $collection->addRoute(new Route($verb, $route, $this->container->get($handler)));
                }
            }

            $this->emitter
                ->emit($collection->find($request)->dispatch($request));
        } catch (Exception $e) {
            $this->container->get(EventDispatcherInterface::class)
                ->dispatch(new SystemError($request, $e, 'EXCEPTION'));
            $this->emitter
                ->emit(new ErrorJsonResponse($e, $e->getCode() > 199 ? $e->getCode() : 400));
        } catch (Error $e) {
            $this->container->get(EventDispatcherInterface::class)
                ->dispatch(new SystemError($request, $e, 'ERROR'));
            $this->emitter
                ->emit(new ErrorJsonResponse($e, $e->getCode() > 199 ? $e->getCode() : 500));
        } catch (Throwable $e) {
            $this->container->get(EventDispatcherInterface::class)
                ->dispatch(new SystemError($request, $e, 'SYSTEM'));
            $this->emitter
                ->emit(new ErrorJsonResponse($e, 500));
        }
    }
}
