<?php

namespace oscarpalmer\Quest\Items;

/**
 * Item class.
 */
class Item
{
    /**
     * @var callable Callback for item.
     */
    private $callback = null;

    /**
     * @var array Request methods for item.
     */
    private $methods = null;

    /**
     * @var string Path for item.
     */
    private $path = null;

    /**
     * Create a new Item object from request methods, path, and callback.
     *
     * @param array    $methods  Request methods for item.
     * @param string   $path     Path for item.
     * @param callable $callback Callback for item.
     */
    public function __construct(array $methods, string $path, callable $callback)
    {
        $this->methods = $methods;
        $this->path = $path;
        $this->callback = $callback;
    }

    /** Public functions. */

    public function getCallback() : callable
    {
        return $this->callback;
    }

    public function getMethods() : array
    {
        return $this->methods;
    }

    public function getPath() : string
    {
        return $this->path;
    }
}
