<?php

namespace oscarpalmer\Quest;

/**
 * Route class.
 */
class Route
{
    /**
     * @var callable Callback for route.
     */
    public $callback;

    /**
     * @var array Request method(s) for route.
     */
    public $methods;

    /**
     * @var string Path for route.
     */
    public $path;

    /**
     * Create a new Request object from request method(s), path, and callback.
     *
     * @param mixed    $method   Request method(s) for route.
     * @param string   $path     Path for route.
     * @param callable $callback Callback for route.
     */
    public function __construct(array $methods, $path, $callback)
    {
        $this->methods = $methods;

        $this->setCallback($callback);
        $this->setPath($path);
    }

    /** Protected functions. */

    /**
     * Set callback if it's a callable.
     *
     * @param  callable $callback Callback to set.
     * @return null
     */
    protected function setCallback($callback)
    {
        if (is_callable($callback)) {
            $this->callback = $callback;

            return null;
        }

        throw new \InvalidArgumentException("Callback must be a callable, " . gettype($callback) . " given.");
    }

    /**
     * Set path if it's a string.
     *
     * @param  string $path Path to set.
     * @return null
     */
    protected function setPath($path)
    {
        if (is_string($path)) {
            $this->path = "/" . trim($path, "/");

            return null;
        }

        throw new \InvalidArgumentException("Path must be a string, " . gettype($path) . " given.");
    }
}
