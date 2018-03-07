<?php

namespace oscarpalmer\Quest\Test;

use oscarpalmer\Quest\Items\Item;

class ItemTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $route = new Item(['GET'], '/', function () {});

        $this->assertNotNull($route);
        $this->assertInstanceOf('oscarpalmer\Quest\Items\Item', $route);
    }

    public function testProperties()
    {
        $callback = function () {};
        $route = new Item(['GET'], '/', $callback);

        $this->assertSame(['GET'], $route->getMethods());
        $this->assertSame('/', $route->getPath());
        $this->assertSame($callback, $route->getCallback());
    }
}
