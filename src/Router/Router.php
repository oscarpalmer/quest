<?php declare(strict_types=1);

namespace oscarpalmer\Quest\Router;

use Exception;

use Nyholm\Psr7\Response;

use oscarpalmer\Quest\Quest;

use function preg_replace;

class Router
{
    /**
     * @var array Array of regular expression patterns for paths
     */
    protected const ROUTE_PATTERNS = ['/\A\/*/u', '/\/*\z/u', '/\//u', '/\./u', '/\((.*?)\)/u', '/\*/u', '/\:([\w\-]+)/u'];

    /**
     * @var array Array of regular expression replacements for paths
     */
    protected const ROUTE_REPLACEMENTS = ['/', '/?', '\/', '\.', '(?:\\1)?', '(.*?)', '([\w\-]+)'];

    protected Quest $quest;

    public array $errors = [];
    public Handler $handler;
    public Routes $routes;

    public function __construct(Quest $quest)
    {
        $this->routes = new Routes();
        $this->handler = new Handler($this);

        $this->quest = $quest;
    }

    public function createErrorResponse(int $status): void
    {
        $response = new Response($status, []);

        if (isset($this->errors[$status])) {
            $response->getBody()->write((string) $this->errors[$status]);
        } else {
            $response->getBody()->write(sprintf('%d %s', $status, $response->getReasonPhrase()));
        }

        $this->quest->response = $response;
    }

    public function dispatch(): void
    {
        $method = $this->quest->request->getMethod();
        $path = $this->quest->request->getUri()->getPath();

        $expression = '';
        $found = false;
        $parameters = [];

        $routes = $this->routes->get($method);

        foreach ($routes as $route) {
            if (
                $this->getExpressionFromPath($route[0], $expression)
                && preg_match($expression, $path, $parameters) === 1
            ) {
                $found = true;

                $this->quest->response->getBody()->write(is_scalar($route[1]) ? (string) $route[1] : json_encode($route[1]));

                break;
            }
        }

        if (!$found) {
            throw new Exception('', in_array($method, ['GET', 'HEAD']) ? 404 : 405);
        }
    }

    protected function getExpressionFromPath(string $path, string &$expression): bool
    {
        $expression = '/\A' . preg_replace(self::ROUTE_PATTERNS, self::ROUTE_REPLACEMENTS, $path) . '\z/u';

        return true;
    }
}
