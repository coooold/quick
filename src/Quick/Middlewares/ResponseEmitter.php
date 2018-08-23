<?php

namespace Quick\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\SapiEmitter;

/**
 * 输出内容
 * Class ResponseEmitter
 * @package Quick\Middlewares
 */
class ResponseEmitter {

    public function __invoke(Request $request, RequestHandlerInterface $next): Response {
        $response = $next->handle($request);
        $emitter = \di(SapiEmitter::class);
        $emitter->emit($response);
        return $response;
    }
}