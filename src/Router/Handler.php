<?php declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

class Handler
{
    protected Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function delete(string $path, mixed $value): self
    {
        $this->router->routes->add('DELETE', $path, $value);

        return $this;
    }

    public function error(int $status, mixed $error): self
    {
        $this->router->errors[$status] = $error;

        return $this;
    }

    public function get(string $path, mixed $value): self
    {
        $this->router->routes->add('GET', $path, $value);

        return $this;
    }

    public function options(string $path, mixed $value): self
    {
        $this->router->routes->add('OPTIONS', $path, $value);

        return $this;
    }

    public function patch(string $path, mixed $value): self
    {
        $this->router->routes->add('PATCH', $path, $value);

        return $this;
    }

    public function post(string $path, mixed $value): self
    {
        $this->router->routes->add('POST', $path, $value);

        return $this;
    }

    public function put(string $path, mixed $value): self
    {
        $this->router->routes->add('PUT', $path, $value);

        return $this;
    }
}
