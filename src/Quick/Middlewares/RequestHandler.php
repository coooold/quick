<?php

namespace Quick\Middlewares;

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Aura\Router\RouterContainer;

/**
 * Class RequestHandler
 * 将请求分发给Controller，并且执行Controller中的中间件
 * @package Quick\Middlewares
 */
class RequestHandler {

    public function __invoke(ServerRequest $request): Response {

        /** @var RouterContainer $routerContainer */
        $routerContainer = \di(RouterContainer::class);

        $route = $routerContainer->getMatcher()->match($request);

        if (!$route) {
            die('404');
        }

        foreach ($route->attributes as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }
        list($className, $method) = $route->handler;
        $request = $request->withAttribute('method', $method);
        /** @var Controller $controllerObj */
        $controllerObj = \di($className);

        // 先执行控制器自带中间件，然后再执行控制器
        $middlewares = $controllerObj->getMiddlewares();
        $queue = [];
        foreach($middlewares as $className) {
            $queue[] = \di($className);
        }
        $queue[] = $controllerObj;

        $dispatcher = new Dispatcher($queue);
        return $dispatcher->handle($request);
    }
}
