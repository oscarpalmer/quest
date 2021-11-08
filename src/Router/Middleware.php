<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

use InvalidArgumentException;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_shift;
use function call_user_func;
use function count;
use function is_string;

class Middleware implements RequestHandlerInterface
{
    protected RequestHandlerInterface $handler;
    protected array $items;

    public function __construct(RequestHandlerInterface $handler)
    {
        $this->handler = $handler;
        $this->items = [];
    }

    public function add(array $items): void
    {
        foreach ($items as $item) {
            if (is_string($item) === false) {
                throw new InvalidArgumentException();
            }

            $this->items[] = $item;
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (count($this->items) === 0) {
            return call_user_func([$this->handler, 'handle'], $request);
        }

        $middleware = array_shift($this->items);

        return call_user_func([new $middleware, 'process'], $request, $this);
    }
}
