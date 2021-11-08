<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router\Info;

use Throwable;

class RouteInfo
{
    protected ?Throwable $error;
    protected RouteInfoUrl $url;

    public function __construct(RouteInfoUrl $url, ?Throwable $throwable)
    {
        $this->error = $throwable;
        $this->url = $url;
    }

    public function getError(): ?Throwable
    {
        return $this->error;
    }

    public function getUrl(): RouteInfoUrl
    {
        return $this->url;
    }
}
