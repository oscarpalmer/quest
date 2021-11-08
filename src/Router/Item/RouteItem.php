<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router\Item;

use function preg_match;
use function preg_replace;

class RouteItem extends BaseItem
{
    protected const EXPRESSION_PREFIX = '/\A';
    protected const EXPRESSION_SUFFIX = '\z/u';

    /**
     * @var array Array of regular expression patterns for paths
     */
    protected const ROUTE_PATTERNS = ['/\A\/*/u', '/\/*\z/u', '/\//u', '/\./u', '/\((.*?)\)/u', '/\*/u', '/\:([\w\-]+)/u'];

    /**
     * @var array Array of regular expression replacements for paths
     */
    protected const ROUTE_REPLACEMENTS = ['/', '/?', '\/', '\.', '(?:\\1)?', '(.*?)', '([\w\-]+)'];

    protected array $middleware;
    protected array $parameters;
    protected string $path;

    public function __construct(string $path, callable|string $callback, ?string $method = null, array $middleware = [])
    {
        parent::__construct($callback, $method);

        $this->middleware = $middleware;
        $this->path = $path;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function match(string $path): bool
    {
        $this->parameters = [];

        return preg_match($this->getExpression($this->path), $path, $this->parameters) === 1;
    }

    protected function getExpression(string $path): string
    {
        return self::EXPRESSION_PREFIX . preg_replace(self::ROUTE_PATTERNS, self::ROUTE_REPLACEMENTS, $path) . self::EXPRESSION_SUFFIX;
    }
}
