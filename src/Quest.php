<?php declare(strict_types = 1);

namespace oscarpalmer\Quest;

use Exception;
use LogicException;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use oscarpalmer\Quest\Router\Router;

use function header;
use function headers_sent;
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

    protected Psr17Factory $factory;
    protected Router $router;

    public ServerRequestInterface $request;
    public ResponseInterface $response;

    public function __construct(ServerRequestInterface $request = null)
    {
        $this->factory = new Psr17Factory();
        $this->router = new Router($this);

        $this->request = $request ?? $this->createRequest();
        $this->response = new Response();
    }

    public function routes(callable $handler): self
    {
        call_user_func($handler, $this->router->handler);

        return $this;
    }

    public function run(): void
    {
        if (headers_sent()) {
            throw new LogicException('');
        }

        try {
            ob_start();

            $this->router->dispatch();
        } catch (Exception $exception) {
            $this->router->createErrorResponse($exception->getCode());
        } finally {
            ob_end_clean();

            $this->finish();
        }
    }

    protected function createRequest(): ServerRequestInterface
    {
        return (new ServerRequestCreator($this->factory, $this->factory, $this->factory, $this->factory))->fromGlobals();
    }

    protected function finish(): void
    {
        $response = $this->response->withHeader('content-length', $this->response->getBody()->getSize());

        $status = $this->response->getStatusCode();

        header(sprintf('HTTP/%s %d %s', $response->getProtocolVersion(), $status, $response->getReasonPhrase()), true, $status);

        foreach ($response->getHeaders() as $header => $values) {
            header(sprintf('%s: %s', $header, join(';', $values)), true, $status);
        }

        echo $response->getBody();
    }
}
