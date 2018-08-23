<?php

namespace Quick\Middlewares;

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Aura\Router\RouterContainer;

/**
 * Class RequestHandler
 * 将请求分发的Controller
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
        $controllerObj = \di($className);

        return $controllerObj($request);
    }
}
