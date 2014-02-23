<?php

namespace oscarpalmer\Quest\Test;

use oscarpalmer\Quest\Quest;
use oscarpalmer\Quest\Route;
use oscarpalmer\Shelf\Request;

class QuestTest extends \PHPUnit_Framework_TestCase
{
    protected $regex_request;
    protected $routes;
    protected $simple_request;

    public function setUp()
    {
        $this->regex_request = new Request(array("REQUEST_URI" => "/a/b.c"));
        $this->simple_request = new Request(array("REQUEST_URI" => "/"));

        $this->routes = array(
            new Route(array("GET"), "/", function () {
                return "index";
            }),
            new Route(array("GET"), "/:a/:b.:c", function () {
                return "a/b.c";
            })
        );
    }

    public function testConstructor()
    {
        $quest = new Quest($this->routes, $this->simple_request);

        $this->assertNotNull($quest);
        $this->assertInstanceOf("oscarpalmer\Quest\Quest", $quest);
    }

    /**
     * @covers oscarpalmer\Quest\Quest::__get
     */
    public function testMagicalGet()
    {
        $quest = new Quest;

        $this->assertInstanceOf("oscarpalmer\Shelf\Request", $quest->request);
        $this->assertInstanceOf("oscarpalmer\Shelf\Response", $quest->response);
        $this->assertInternalType("array", $quest->routes);
        $this->assertNull($quest->not_a_property);
    }

    /**
     * @covers oscarpalmer\Quest\Quest::addRoute
     * @covers oscarpalmer\Quest\Quest::delete
     * @covers oscarpalmer\Quest\Quest::get
     * @covers oscarpalmer\Quest\Quest::post
     * @covers oscarpalmer\Quest\Quest::put
     */
    public function testAddRoutes()
    {
        $callback = function () {};
        $quest = new Quest;

        $quest->delete("/", $callback);
        $quest->get("/", $callback);
        $quest->post("/", $callback);
        $quest->put("/", $callback);

        $this->assertInternalType("array", $quest->routes);
        $this->assertCount(4, $quest->routes);
    }

    /**
     * @covers oscarpalmer\Quest\Quest::callback
     * @covers oscarpalmer\Quest\Quest::router
     * @covers oscarpalmer\Quest\Quest::run
     */
    public function testErrorRun()
    {
        $quest = new Quest(array(), $this->simple_request);
        $quest->run();

        $this->expectOutputString("404 Not Found");
    }

    /**
     * @covers oscarpalmer\Quest\Quest::callback
     * @covers oscarpalmer\Quest\Quest::pathToRegex
     * @covers oscarpalmer\Quest\Quest::router
     * @covers oscarpalmer\Quest\Quest::run
     */
    public function testRegexRun()
    {
        $quest = new Quest($this->routes, $this->regex_request);
        $quest->run();

        $this->expectOutputString("a/b.c");
    }

    /**
     * @covers oscarpalmer\Quest\Quest::callback
     * @covers oscarpalmer\Quest\Quest::router
     * @covers oscarpalmer\Quest\Quest::run
     */
    public function testSimpleRun()
    {
        $quest = new Quest($this->routes, $this->simple_request);
        $quest->run();

        $this->expectOutputString("index");
    }
}
