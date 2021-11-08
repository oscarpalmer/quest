<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router\Item;

class RouteItem extends BaseItem
{
    protected string $path;

    public function __construct(string $path, callable|string $callback, ?string $method = null)
    {
        parent::__construct($callback, $method);

        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
