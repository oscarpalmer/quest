<?php declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

use Throwable;

class RouteInfo
{
    protected ?Throwable $error;
    protected RouteUrl $url;

    public function __construct(RouteUrl $url, ?Throwable $throwable)
    {
        $this->error = $throwable;
        $this->url = $url;
    }

    public function getError(): ?Throwable
    {
        return $this->error;
    }

    public function getUrl(): RouteUrl
    {
        return $this->url;
    }
}