<?php

namespace oscarpalmer\Quest\Test;

use oscarpalmer\Quest\Route;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $route = new Route(array("GET"), "/", function () {});

        $this->assertNotNull($route);
        $this->assertInstanceOf("oscarpalmer\Quest\Route", $route);
    }

    /**
     * @covers oscarpalmer\Quest\Route::setCallback
     * @covers oscarpalmer\Quest\Route::setPath
     */
    public function testSetProperties()
    {
        $callback = function () {};
        $route = new Route(array("GET"), "/", $callback);

        $this->assertSame(array("GET"), $route->methods);
        $this->assertSame("/", $route->path);
        $this->assertSame($callback, $route->callback);

        try {
            $route = new Route(array("GET"), "/", null);
        } catch (\InvalidArgumentException $e) {
            $this->assertNotNull($e);
            $this->assertInstanceOf("\InvalidArgumentException", $e);
        }

        try {
            $route = new Route(array("GET"), null, $callback);
        } catch (\InvalidArgumentException $e) {
            $this->assertNotNull($e);
            $this->assertInstanceOf("\InvalidArgumentException", $e);
        }
    }
}
