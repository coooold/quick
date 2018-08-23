<?php

use function DI\create;
use function DI\get;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Aura\Router\RouterContainer;
use Psr\SimpleCache\CacheInterface;
use Desarrolla2\Cache\Adapter\File as Cache;

return [
    RouterContainer::class => function ($container)
    {
        $routerContainer = new RouterContainer();
        $map = $routerContainer->getMap();

        $routerConfs = $container->get('router');
        foreach ($routerConfs as $name => $conf) {
            $map->route($name, $conf['path'], $conf['handler'])->allows($conf['method']);
        }
        return $routerContainer;
    },
    LoggerInterface::class => create(Logger::class)
        ->constructor('main')
        ->method('pushHandler', new RotatingFileHandler(__DIR__ . '/../log/ideafun', 100)),

    CacheInterface::class => function ($container)
    {
        $runtimePath = $container->get('runtime_path');
        if (!$runtimePath || !is_dir($runtimePath)) {
            throw new \Exception('runtime_path is not configured');
        }
        $cachePath = $runtimePath . '/cache';
        if (!file_exists($cachePath)) {
            mkdir($cachePath);
        }
        return new Cache($cachePath);
    },
    LoggerInterface::class => function ($container)
    {
        $runtimePath = $container->get('runtime_path');
        if (!$runtimePath || !is_dir($runtimePath)) {
            throw new \Exception('runtime_path is not configured');
        }
        $logPath = $runtimePath . '/logs';
        if (!file_exists($logPath)) {
            mkdir($logPath);
        }
        $logger = new Logger('main');
        $logger->pushHandler(new RotatingFileHandler($logPath . "/quick", 100));

        if (\Quick\Quick::isCli()) {
            $logger->pushHandler(new StreamHandler(STDOUT));
        }

        return $logger;
    },
];
