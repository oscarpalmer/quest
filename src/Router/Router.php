<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

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
use function sprintf;

class Router implements RequestHandlerInterface
{
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

        $routes = $this->routes->get($method);

        foreach ($routes as $route) {
            if ($route->match($path)) {
                return (new Middleware(function ($request) use ($path, $route) {
                    return $this->getResponse($request, $route, new RouteInfoUrl($path, $route->getPath(), $route->getParameters()), null);
                }))->add($route->getMiddleware())->handle($request);
            }
        }

        if (in_array($method, ['GET', 'HEAD'])) {
            throw new NotFoundException();
        }

        throw new MethodNotAllowedException();
    }

    public function getErrorResponse(ServerRequestInterface $request, int $status, Throwable $throwable = null): ResponseInterface
    {
        if (isset($this->errors[$status])) {
            $routeInfoUrl = new RouteInfoUrl($request->getUri()->getPath());

            return $this->getResponse($request, $this->errors[$status], $routeInfoUrl, $throwable);
        }

        $response = new Response($status);

        $response->getBody()->write(sprintf('%d %s<br><br>%s', $status, $response->getReasonPhrase(), (string) $throwable ?? ''));

        return $response;
    }

    protected function getResponse(ServerRequestInterface $request, BaseItem $item, RouteInfoUrl $routeInfoUrl, ?Throwable $throwable): ResponseInterface
    {
        return call_user_func($item->getCallback(), $request, new RouteInfo($routeInfoUrl, $throwable));
    }
}
