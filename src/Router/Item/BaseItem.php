<?php declare(strict_types=1);

namespace oscarpalmer\Quest\Router\Item;

abstract class BaseItem
{
    protected mixed $callback;
    protected ?string $method;

    public function __construct(callable|string $callback, ?string $method = null)
    {
        $this->callback = $callback;
        $this->method = $method;
    }

    public function getCallback(): callable|string
    {
        return $this->callback;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }
}
