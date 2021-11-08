<?php

declare(strict_types=1);

namespace oscarpalmer\Quest;

use Throwable;
use LogicException;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use oscarpalmer\Quest\Exception\ErrorException;
use oscarpalmer\Quest\Router\Middleware;
use oscarpalmer\Quest\Router\Router;

use function call_user_func;
use function header;
use function headers_sent;
use function join;
use function ob_end_clean;
use function ob_start;
use function sprintf;

/**
 * Quest
 */
class Quest implements RequestHandlerInterface
{
    /**
     * @var string Current version
     */
    const VERSION = '3.0.0';

    protected static Psr17Factory $factory;

    protected Middleware $middleware;
    protected Router $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->middleware = new Middleware($this->router);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = null;

        try {
            ob_start();

            $response = $this->middleware->handle($request);
        } catch (ErrorException $exception) {
            $response = $this->router->getErrorResponse($request, $exception->getStatus());
        } catch (Throwable $throwable) {
            $response = $this->router->getErrorResponse($request, 500, $throwable);
        } finally {
            ob_end_clean();

            return $response;
        }
    }

    public function middleware(array $items): self
    {
        $this->middleware->add($items);

        return $this;
    }

    public function routes(callable $handler): self
    {
        call_user_func($handler, $this->router->handler);

        return $this;
    }

    public function run(ServerRequestInterface $request = null): void
    {
        if (headers_sent()) {
            throw new LogicException();
        }

        $this->finish($this->handle($request ?? $this->createRequest()));
    }

    protected function createRequest(): ServerRequestInterface
    {
        $factory = self::getFactory();

        return (new ServerRequestCreator($factory, $factory, $factory, $factory))->fromGlobals();
    }

    protected function finish(ResponseInterface $response): void
    {
        $response = $response->withHeader('content-length', $response->getBody()->getSize());

        $protocol = $response->getProtocolVersion();
        $reasonPhrase = $response->getReasonPhrase();
        $status = $response->getStatusCode();

        header(sprintf('HTTP/%s %d %s', $protocol, $status, $reasonPhrase), true, $status);

        foreach ($response->getHeaders() as $header => $values) {
            header(sprintf('%s: %s', $header, join(';', $values)), true, $status);
        }

        echo $response->getBody();
    }

    public static function createResponse(string $body, array $headers = []): ResponseInterface
    {
        $response = self::getFactory()->createResponse();

        $response->getBody()->write($body);

        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }

    protected static function getFactory(): Psr17Factory
    {
        if (isset(self::$factory)) {
            return self::$factory;
        }

        self::$factory = new Psr17Factory();

        return self::$factory;
    }
}
