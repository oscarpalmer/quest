<?php

declare(strict_types=1);

namespace oscarpalmer\Quest\Router\Info;

use stdClass;

use function array_shift;
use function count;
use function is_array;
use function ltrim;
use function mb_strtolower;
use function preg_match_all;

class RouteUrl
{
    protected string $path;

    protected stdClass $values;

    protected array $wildcards;

    public function __construct(string $urlPath, string $routePath = null, array $parameters = [])
    {
        $this->path = $urlPath;
        $this->values = new \stdClass;
        $this->wildcards = [];

        if (count($parameters) > 1) {
            $this->setParameters($routePath, $parameters);
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getValues(): \stdClass
    {
        return $this->values;
    }

    public function getWildcards(): array
    {
        return $this->wildcards;
    }

    protected function setParameter(string $key, ?string $value): void
    {
        $key = ltrim($key, ':');

        if ($key === '*') {
            $this->wildcards[] = $value;
        } else {
            $key = mb_strtolower($key, 'UTF-8');

            if (isset($this->values->$key)) {
                if (is_array($this->values->$key)) {
                    $this->values->$key[] = $value;
                } else {
                    $this->values->$key = [$this->values->$key, $value];
                }
            } else {
                $this->values->$key = $value;
            }
        }
    }

    protected function setParameters(string $path, array $parameters): void
    {
        array_shift($parameters);

        preg_match_all('/(\:\w+|\*)/u', $path, $keys);

        foreach ($keys[0] as $index => $key) {
            $this->setParameter($key, $parameters[$index] ?? null);
        }
    }
}
