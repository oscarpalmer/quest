<?php

namespace oscarpalmer\Quest\Test;

use oscarpalmer\Quest\Item;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $route = new Item(array("GET"), "/", function () {});

        $this->assertNotNull($route);
        $this->assertInstanceOf("oscarpalmer\Quest\Item", $route);
    }

    /**
     * @covers oscarpalmer\Quest\Item::setCallback
     * @covers oscarpalmer\Quest\Item::setPath
     */
    public function testSetProperties()
    {
        $callback = function () {};
        $route = new Item(array("GET"), "/", $callback);

        $this->assertSame(array("GET"), $route->methods);
        $this->assertSame("/", $route->path);
        $this->assertSame($callback, $route->callback);

        try {
            $route = new Item(array("GET"), "/", null);
        } catch (\InvalidArgumentException $e) {
            $this->assertNotNull($e);
            $this->assertInstanceOf("\InvalidArgumentException", $e);
        }

        try {
            $route = new Item(array("GET"), null, $callback);
        } catch (\InvalidArgumentException $e) {
            $this->assertNotNull($e);
            $this->assertInstanceOf("\InvalidArgumentException", $e);
        }
    }
}
