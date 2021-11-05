<?php declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

use oscarpalmer\Quest\Router\Item\RouteItem;

class Routes
{
    protected array $DELETE = [];
    protected array $GET = [];
    protected array $HEAD = [];
    protected array $OPTIONS = [];
    protected array $PATCH = [];
    protected array $POST = [];
    protected array $PUT = [];

    public function add(string $verb, string $path, callable|string $callback, ?string $method = null): void
    {
        $this->{$verb}[] = new RouteItem($path, $callback, $method);
    }

    public function get(string $verb): array
    {
        if ($verb === 'HEAD') {
            return $this->GET;
        }

        return $this->{$verb} ?? [];
    }
}
