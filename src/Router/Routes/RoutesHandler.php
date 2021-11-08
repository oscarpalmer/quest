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

    public function delete(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        $this->router->routes->add('DELETE', $path, $callback, $method);

        return $this;
    }

    public function error(int $status, callable|string $callback, string $method = null): self
    {
        $this->router->errors[$status] = new ErrorItem($callback, $method);

        return $this;
    }

    public function get(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        $this->router->routes->add('GET', $path, $callback, $method, $middleware);

        return $this;
    }

    public function options(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        $this->router->routes->add('OPTIONS', $path, $callback, $method, $middleware);

        return $this;
    }

    public function patch(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        $this->router->routes->add('PATCH', $path, $callback, $method, $middleware);

        return $this;
    }

    public function post(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        $this->router->routes->add('POST', $path, $callback, $method, $middleware);

        return $this;
    }

    public function put(string $path, callable|string $callback, string $method = null, array $middleware = []): self
    {
        $this->router->routes->add('PUT', $path, $callback, $method, $middleware);

        return $this;
    }
}
