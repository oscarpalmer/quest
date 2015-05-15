<?php

namespace oscarpalmer\Quest;

/**
 * Item class.
 */
class Item
{
    /**
     * @var callable Callback for item.
     */
    public $callback;

    /**
     * @var array Request method(s) for item.
     */
    public $methods;

    /**
     * @var string Path for item.
     */
    public $path;

    /**
     * Create a new Item object from request method(s), path, and callback.
     *
     * @param mixed    $method   Request method(s) for item.
     * @param string   $path     Path for item.
     * @param callable $callback Callback for item.
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
     */
    protected function setCallback($callback)
    {
        if (is_callable($callback)) {
            $this->callback = $callback;

            return;
        }

        throw new \InvalidArgumentException(
            "Callback must be of type \"callable\", \"" .
            gettype($callback) .
            "\" given."
        );
    }

    /**
     * Set path if it's a string.
     *
     * @param  string $path Path to set.
     */
    protected function setPath($path)
    {
        if (is_string($path)) {
            $this->path = "/" . trim($path, "/");

            return;
        }

        throw new \InvalidArgumentException(
            "Path must be of type \"string\", \"" .
            gettype($path) .
            "\" given."
        );
    }
}
