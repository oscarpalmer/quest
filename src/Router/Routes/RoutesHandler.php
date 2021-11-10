<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router\Routes;

use oscarpalmer\Quest\Router\Router;
use oscarpalmer\Quest\Router\Item\ErrorItem;

class RoutesHandler
{
    protected Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function add(string $verb, string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        $this->router->routes->add($verb, $path, $callback, $method, $middleware);

        return $this;
    }

    public function connect(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        return $this->add('CONNECT', $path, $callback, $method, $middleware);
    }

    public function delete(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        return $this->add('DELETE', $path, $callback, $method, $middleware);
    }

    public function error(int $status, callable|string $callback, string $method = null): self
    {
        $this->router->errors[$status] = new ErrorItem($callback, $method);

        return $this;
    }

    public function get(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        return $this->add('GET', $path, $callback, $method, $middleware);
    }

    public function options(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        return $this->add('OPTIONS', $path, $callback, $method, $middleware);
    }

    public function patch(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        return $this->add('PATCH', $path, $callback, $method, $middleware);
    }

    public function post(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        return $this->add('POST', $path, $callback, $method, $middleware);
    }

    public function put(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        return $this->add('PUT', $path, $callback, $method, $middleware);
    }

    public function trace(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        return $this->add('TRACE', $path, $callback, $method, $middleware);
    }
}
