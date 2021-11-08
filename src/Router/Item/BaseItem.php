<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router\Item;

use function is_callable;

abstract class BaseItem
{
    protected mixed $callback;
    protected ?string $method;

    public function __construct(callable|string $callback, ?string $method = null)
    {
        $this->callback = $callback;
        $this->method = $method;
    }

    public function getCallback(): callable
    {
        if (is_callable($this->callback)) {
            return $this->callback;
        }

        return [new $this->callback, $this->method ?? 'handle'];
    }
}
