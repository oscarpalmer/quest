<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router\Middleware;

use InvalidArgumentException;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_shift;
use function call_user_func;
use function count;
use function is_string;

class MiddlewareHandler implements RequestHandlerInterface
{
    protected mixed $handler;
    protected array $items;

    public function __construct(callable|RequestHandlerInterface $handler)
    {
        $this->items = [];

        $this->setHandler($handler);
    }

    public function add(array $items): self
    {
        foreach ($items as $item) {
            if (is_string($item) === false) {
                throw new InvalidArgumentException();
            }

            $this->items[] = $item;
        }

        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (count($this->items) === 0) {
            return call_user_func($this->handler, $request);
        }

        $middleware = array_shift($this->items);

        return call_user_func([new $middleware, 'process'], $request, $this);
    }

    protected function setHandler(callable|RequestHandlerInterface $handler): void
    {
        if (is_callable($handler)) {
            $this->handler = $handler;

            return;
        }

        $this->handler = [$handler, 'handle'];
    }
}
