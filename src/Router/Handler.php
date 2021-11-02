<?php declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

class Handler
{
    protected Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
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
}
