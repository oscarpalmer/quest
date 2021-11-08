<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

use LogicException;
use Throwable;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use oscarpalmer\Quest\Exception\MethodNotAllowedException;
use oscarpalmer\Quest\Exception\NotFoundException;
use oscarpalmer\Quest\Router\Info\RouteInfo;
use oscarpalmer\Quest\Router\Info\RouteInfoUrl;
use oscarpalmer\Quest\Router\Item\BaseItem;
use oscarpalmer\Quest\Router\Routes\Routes;
use oscarpalmer\Quest\Router\Routes\RoutesHandler;

use function call_user_func;
use function in_array;
use function is_callable;
use function preg_match;
use function preg_replace;
use function sprintf;

class Router implements RequestHandlerInterface
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

    public array $errors;
    public RoutesHandler $handler;
    public Routes $routes;

    public function __construct()
    {
        $this->errors = [];
        $this->handler = new RoutesHandler($this);
        $this->routes = new Routes();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $expression = '';
        $parameters = [];

        $routes = $this->routes->get($method);

        foreach ($routes as $route) {
            $routePath = $route->getPath();

            if (
                $this->getExpressionFromPath($routePath, $expression)
                && preg_match($expression, $path, $parameters) === 1
            ) {
                $routeInfoUrl = new RouteInfoUrl($path, $routePath, $parameters);

                return $this->getResponse($request, $route, $routeInfoUrl, null);
            }
        }

        if (in_array($method, ['GET', 'HEAD'])) {
            throw new NotFoundException();
        }

        throw new MethodNotAllowedException();
    }

    public function getErrorResponse(
        ServerRequestInterface $request,
        int $status,
        Throwable $throwable = null
    ): ResponseInterface {
        if (isset($this->errors[$status])) {
            $routeInfoUrl = new RouteInfoUrl($request->getUri()->getPath());

            return $this->getResponse($request, $this->errors[$status], $routeInfoUrl, $throwable);
        }

        $response = new Response($status);

        $response->getBody()->write(sprintf('%d %s<br><br>%s', $status, $response->getReasonPhrase(), (string) $throwable ?? ''));

        return $response;
    }

    protected function getResponse(
        ServerRequestInterface $request,
        BaseItem $item,
        RouteInfoUrl $routeInfoUrl,
        ?Throwable $throwable
    ): ResponseInterface {
        $routeInfo = new RouteInfo($routeInfoUrl, $throwable);

        $response = call_user_func($this->getResponseCallback($item), $request, $routeInfo);

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        throw new LogicException();
    }

    protected function getResponseCallback(BaseItem $item): mixed
    {
        $callback = $item->getCallback();

        if (is_callable($callback)) {
            return $callback;
        }

        return [new $callback, $item->getMethod() ?? 'handle'];
    }

    protected function getExpressionFromPath(string $path, string &$expression): bool
    {
        $expression = self::EXPRESSION_PREFIX . preg_replace(self::ROUTE_PATTERNS, self::ROUTE_REPLACEMENTS, $path) . self::EXPRESSION_SUFFIX;

        return true;
    }
}
