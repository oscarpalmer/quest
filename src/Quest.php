<?php declare(strict_types = 1);

namespace oscarpalmer\Quest;

use Throwable;
use LogicException;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use oscarpalmer\Quest\Exception\ErrorException;
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
class Quest
{
    /**
     * @var string Current version
     */
    const VERSION = '3.0.0';

    protected static Psr17Factory $factory;

    protected Context $context;
    protected Router $router;

    public function __construct(ServerRequestInterface $request = null)
    {
        $this->context = new Context($request ?? $this->createRequest(), new Response());
        $this->router = new Router($this->context);
    }

    public function routes(callable $handler): self
    {
        call_user_func($handler, $this->router->handler);

        return $this;
    }

    public function run(): void
    {
        if (headers_sent()) {
            throw new LogicException();
        }

        try {
            ob_start();

            $this->router->dispatch();
        } catch (ErrorException $exception) {
            $this->router->getErrorResponse($exception->getStatus());
        } catch (Throwable $throwable) {
            $this->router->getErrorResponse(500, $throwable);
        } finally {
            ob_end_clean();

            $this->finish();
        }
    }

    protected function createRequest(): ServerRequestInterface
    {
        $factory = self::getFactory();

        return (new ServerRequestCreator($factory, $factory, $factory, $factory))->fromGlobals();
    }

    protected function finish(): void
    {
        $response = $this->context->response;
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
