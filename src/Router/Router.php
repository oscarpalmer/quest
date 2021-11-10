<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

use Throwable;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use oscarpalmer\Quest\Http\Exception\MethodNotAllowedException;
use oscarpalmer\Quest\Http\Exception\NotFoundException;
use oscarpalmer\Quest\Router\Info\RouteUrl;
use oscarpalmer\Quest\Router\Item\RouteItem;
use oscarpalmer\Quest\Router\Middleware\MiddlewareHandler;
use oscarpalmer\Quest\Router\Routes\RoutesCollection;
use oscarpalmer\Quest\Router\Routes\RoutesHandler;

use function call_user_func;
use function in_array;
use function sprintf;

class Router implements RequestHandlerInterface
{
    public array $errors;
    public RoutesHandler $handler;
    public RoutesCollection $routes;

    public function __construct()
    {
        $this->errors = [];
        $this->handler = new RoutesHandler($this);
        $this->routes = new RoutesCollection();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $routes = $this->routes->get($method);

        foreach ($routes as $route) {
            if ($route->match($path)) {
                return $this->getResponse($request, $route);
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
            return call_user_func($this->errors[$status]->getCallback(), $request, $throwable);
        }

        $response = new Response($status);

        $responseBody = sprintf('%d %s<br><br>%s', $status, $response->getReasonPhrase(), (string) $throwable ?? '');

        $response->getBody()->write($responseBody);

        return $response;
    }

    protected function getResponse(ServerRequestInterface $request, RouteItem $route): ResponseInterface
    {
        $middleware = new MiddlewareHandler(function (ServerRequestInterface $request) use ($route) {
            $routeUrl = new RouteUrl($request->getUri()->getPath(), $route->getPath(), $route->getParameters());

            return call_user_func($route->getCallback(), $request, $routeUrl);
        });

        return $middleware->add($route->getMiddleware())->handle($request);
    }
}
