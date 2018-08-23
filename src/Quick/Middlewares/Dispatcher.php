<?php

namespace Quick\Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 * A reusable PSR-15 request handler.
 */
class Dispatcher implements RequestHandlerInterface {

    protected $queue;

    /**
     * Constructor.
     * @param array|Traversable $queue A queue of middleware entries.
     * @throws \Exception
     */
    public function __construct($queue) {
        if (!is_iterable($queue)) {
            throw new \Exception('\$queue must be array or Traversable.');
        }

        $this->queue = $queue;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $middleware = current($this->queue);
        next($this->queue);

        return $middleware($request, $this);
    }
}
