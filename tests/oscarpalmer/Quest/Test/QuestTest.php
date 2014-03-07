<?php

namespace oscarpalmer\Quest\Test;

use oscarpalmer\Quest\Quest;
use oscarpalmer\Quest\Item;
use oscarpalmer\Quest\Exception\Halt;
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
            new Item(array("GET"), "/", function () {
                return "index";
            }),
            new Item(array("GET"), "/:a/:b.:c", function () {
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
        $this->assertInternalType("array", $quest->errors);
        $this->assertInternalType("array", $quest->filters);
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

    public function testCustomError()
    {
        $quest = new Quest(array(), $this->simple_request);
        $quest->error(404, function () { return "Custom error."; });

        try {
            $quest->error(404);
        } catch (Halt $e) {
            $this->assertNotNull($e);
            $this->assertInstanceOf("oscarpalmer\Quest\Exception\Halt", $e);
            $this->assertSame("Custom error.", $e->getMessage());
        }
    }

    public function testBadErrors()
    {
        $quest = new Quest(array(), $this->simple_request);

        foreach (array(
            500 => "bad",
            "bad" => function () {}
        ) as $status => $callback) {
            try {
                $quest->error($status, $callback);
            } catch (\Exception $e) {
                $this->assertNotNull($e);
                $this->assertInstanceOf("InvalidArgumentException", $e);
            }
        }
    }

    public function testBadFilters()
    {
        $quest = new Quest(array(), $this->simple_request);

        foreach (array(
            "after" => null,
            "before" => null
        ) as $fn => $callback) {
            try {
                $quest->$fn($callback);
            } catch (\Exception $e) {
                $this->assertNotNull($e);
                $this->assertInstanceOf("InvalidArgumentException", $e);
            }
        }
    }

    /**
     * @covers oscarpalmer\Quest\Quest::callback
     * @covers oscarpalmer\Quest\Quest::errorCallback
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
     * @covers oscarpalmer\Quest\Quest::addFilter
     * @covers oscarpalmer\Quest\Quest::after
     * @covers oscarpalmer\Quest\Quest::before
     * @covers oscarpalmer\Quest\Quest::filter
     */
    public function testFilters()
    {
        $quest = new Quest($this->routes, $this->regex_request);

        $quest->after(function () { return " after"; });
        $quest->before(function () { return "before "; });
        $quest->before("/a/b.c", function () { return "path_before "; });

        $quest->run();

        $this->expectOutputString("before path_before a/b.c after");
    }

    public function testHalt()
    {
        $quest = new Quest(array(), $this->simple_request);

        $quest->get("/", function ($quest) {
            $quest->halt(406);
        });

        $quest->run();

        $this->expectOutputString("406 Not Acceptable");
    }

    public function testHaltCustom()
    {
        $quest = new Quest(array(), $this->simple_request);

        $quest->get("/", function ($quest) {
            $quest->halt(406, "Boo!");
        });

        $quest->run();

        $this->expectOutputString("Boo!");
    }

    public function testRedirect()
    {
        $quest = new Quest(array(), $this->simple_request);

        try {
            $quest->redirect("/a/b.c");
        } catch (Halt $e) {
            $this->assertNotNull($e);
            $this->assertInstanceOf("oscarpalmer\Quest\Exception\Halt", $e);

            $this->assertSame("302 Found", $e->getMessage());
        }
    }

    public function testRedirectError()
    {
        $quest = new Quest(array(), $this->simple_request);

        try {
            $quest->redirect(null);
        } catch (\Exception $e) {
            $this->assertNotNull($e);
            $this->assertInstanceOf("InvalidArgumentException", $e);
        }
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
