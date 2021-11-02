<?php declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

class Routes
{
    protected array $DELETE = [];
    protected array $GET = [];
    protected array $HEAD = [];
    protected array $OPTIONS = [];
    protected array $PATCH = [];
    protected array $POST = [];
    protected array $PUT = [];

    public function add(string $method, string $path, mixed $value): void
    {
        $this->{$method}[] = [$path, $value];
    }

    public function get(string $method): array
    {
        if ($method === 'HEAD') {
            return $this->GET;
        }

        return $this->{$method} ?? [];
    }
}
