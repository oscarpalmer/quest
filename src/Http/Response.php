<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Http;

use InvalidArgumentException;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Response
{
    protected const JSON_OPTIONS = JSON_INVALID_UTF8_SUBSTITUTE
        | JSON_PRESERVE_ZERO_FRACTION
        | JSON_THROW_ON_ERROR
        | JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE;

    protected static Psr17Factory $factory;

    public static function html(mixed $body, array $headers = [], int $status = 200): ResponseInterface
    {
        if (is_scalar($body) === false) {
            throw new InvalidArgumentException();
        }

        return self::create($status, $body, $headers)->withAddedHeader('content-type', 'text/html');
    }

    public static function json(mixed $data, array $headers = [], int $status = 200): ResponseInterface
    {
        return self::create($status, json_encode($data, self::JSON_OPTIONS), $headers)->withAddedHeader('content-type', 'application/json');
    }

    public static function redirect(string|UriInterface $uri, array $headers = [], int $status = 302): ResponseInterface
    {
        return self::create($status, '', $headers)->withHeader('location', (string) $uri);
    }

    protected static function create(int $status, string $body, array $headers): ResponseInterface
    {
        $response = self::getFactory()->createResponse($status);

        foreach ($headers as $header => $value) {
            $response = $response->withAddedHeader($header, $value);
        }

        $response->getBody()->write($body);

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
