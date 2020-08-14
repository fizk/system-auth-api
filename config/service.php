<?php

use Interop\Container\ContainerInterface;

use Auth\Handler;
use Auth\Service;
use Auth\Event;

use MongoDB\Client;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Phly\EventDispatcher\EventDispatcher;
use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

return [
    'factories' => [
        Handler\Index::class => function(ContainerInterface $container, $requestedName) {
            return new Handler\Index();
        },
        Handler\Login::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\Login())
                ->setUserService($container->get(Service\User::class))
                ->setKeyService($container->get(Service\Key::class))
                ;
        },
        Handler\Refresh::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\Refresh())
                ->setUserService($container->get(Service\User::class))
                ->setKeyService($container->get(Service\Key::class))
                ;
        },
        Handler\Authenticate::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\Authenticate())
                ->setCredentialsService($container->get(Service\Credentials::class))
                ;
        },
        Handler\Create::class => function(ContainerInterface $container, $requestedName) {
            return (new Handler\Create())
                ->setCredentialsService($container->get(Service\Credentials::class))
                ;
        },

        Service\User::class => function(ContainerInterface $container, $requestedName) {
            return (new Service\User())
                ->setDriver($container->get(Service\DatabaseAware::class))
                ->setEventDispatcher($container->get(EventDispatcherInterface::class))
                ;
        },
        Service\Credentials::class => function(ContainerInterface $container, $requestedName) {
            return (new Service\Credentials())
                ->setDriver($container->get(Service\DatabaseAware::class))
                ->setEventDispatcher($container->get(EventDispatcherInterface::class))
                ;
        },
        Service\DatabaseAware::class => function (ContainerInterface $container, $requestedName) {
            $db = getenv('DB_DATABASE') ? : 'auth';
            $host = getenv('DB_HOST') ? : 'localhost';
            $port = getenv('DB_PORT') ? : 27017;
            $user = getenv('DB_USER') ? rawurlencode(getenv('DB_USER')) : null;
            $pwd = getenv('DB_PASSWORD') ? rawurlencode(getenv('DB_PASSWORD')) : null;

            return (new MongoDB\Client(
                $user && $pwd
                    ? "mongodb://{$user}:{$pwd}@{$host}:{$port}/{$db}"
                    : "mongodb://{$host}:{$port}/{$db}"
            ))->selectDatabase($db);
        },
        Service\Key::class => function (ContainerInterface $container, $requestedName) {
            return (new Service\Key())
                ->setEventDispatcher($container->get(EventDispatcherInterface::class))
                ;
        },

        EventDispatcherInterface::class => function (ContainerInterface $container, $requestedName) {
            $logger = $container->get(LoggerInterface::class);
            $provider = new AttachableListenerProvider();
            // $provider->listen(Event\ServiceError::class, function (Event\ServiceError $event) use ($logger) : void {
            //     $logger->error((string) $event);
            // });
            // $provider->listen(Event\EntryView::class, function (Event\EntryView $event) use ($logger) : void {
            //     $logger->info((string) $event);
            // });
            // $provider->listen(Event\SystemError::class, function (Event\SystemError $event) use ($logger) : void {
            //     $logger->error((string) $event);
            // });

            return new EventDispatcher($provider);
        },
        LoggerInterface::class => function (ContainerInterface $container, $requestedName) {
            $log = new Logger('user-api');
            $log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
            return $log;
        },
    ],
];
