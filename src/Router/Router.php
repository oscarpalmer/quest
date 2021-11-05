<?php declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

use LogicException;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

use oscarpalmer\Quest\Context;
use oscarpalmer\Quest\Exception\ErrorException;
use oscarpalmer\Quest\Router\Item\BaseItem;

use function call_user_func;
use function in_array;
use function is_callable;
use function preg_match;
use function preg_replace;
use function sprintf;

class Router
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

    protected Context $context;

    public array $errors = [];
    public Handler $handler;
    public Routes $routes;

    public function __construct(Context $context)
    {
        $this->routes = new Routes();
        $this->handler = new Handler($this);

        $this->context = $context;
    }

    public function dispatch(): void
    {
        $method = $this->context->request->getMethod();
        $path = $this->context->request->getUri()->getPath();

        $expression = '';
        $found = false;
        $parameters = [];

        $routes = $this->routes->get($method);

        foreach ($routes as $route) {
            if (
                $this->getExpressionFromPath($route->getPath(), $expression)
                && preg_match($expression, $path, $parameters) === 1
            ) {
                $found = true;

                $this->context->response = $this->getResponse($route);

                break;
            }
        }

        if (!$found) {
            throw new ErrorException(in_array($method, ['GET', 'HEAD']) ? 404 : 405);
        }
    }

    public function getErrorResponse(int $status): void
    {
        if (isset($this->errors[$status])) {
            $this->context->response = $this->getResponse($this->errors[$status]);

            return;
        }

        $response = new Response($status);

        $response->getBody()->write(sprintf('%d %s', $status, $response->getReasonPhrase()));

        $this->context->response = $response;
    }

    protected function getResponse(BaseItem $item): ResponseInterface
    {
        $response = call_user_func($this->getResponseCallback($item), $this->context->request);

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        throw new LogicException();
    }

    protected function getResponseCallback(BaseItem $item): mixed
    {
        if (is_callable($item->getCallback())) {
            return $item->getCallback();
        }

        return [new ($item->getCallback()), $item->getMethod() ?? '__invoke'];
    }

    protected function getExpressionFromPath(string $path, string &$expression): bool
    {
        $expression = self::EXPRESSION_PREFIX . preg_replace(self::ROUTE_PATTERNS, self::ROUTE_REPLACEMENTS, $path) . self::EXPRESSION_SUFFIX;

        return true;
    }
}
