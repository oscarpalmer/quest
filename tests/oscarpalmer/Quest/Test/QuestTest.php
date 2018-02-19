<?php

namespace oscarpalmer\Quest\Test;

use oscarpalmer\Quest\Quest;
use oscarpalmer\Quest\Items\Item;
use oscarpalmer\Quest\Items\Filter;
use oscarpalmer\Quest\Items\Route;
use oscarpalmer\Quest\Exception\Halt;
use oscarpalmer\Shelf\Request;

class QuestTest extends \PHPUnit\Framework\TestCase
{
    protected $regex;
    protected $routes;
    protected $simple;

    public function setUp()
    {
        $this->regex = ["REQUEST_URI" => "/a/b.c"];
        $this->simple = ["REQUEST_URI" => "/"];

        $this->routes = [
            new Route(["GET"], "/", function () {
                return "index";
            }),
            new Route(["GET"], "/*/:b.:c", function () {
                return "a/b.c";
            })
        ];
    }

    public function testConstructor()
    {
        $quest = new Quest($this->routes, new Request($this->simple));

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
        $quest = new Quest([], new Request($this->simple));
        $quest->error(404, function () { return "Custom error."; });

        try {
            $quest->error(404);
        } catch (Halt $e) {
            $this->assertNotNull($e);
            $this->assertInstanceOf("oscarpalmer\Quest\Exception\Halt", $e);
            $this->assertSame("Custom error.", $e->getMessage());
        }
    }

    /**
     * @covers oscarpalmer\Quest\Quest::errorCallback
     * @covers oscarpalmer\Quest\Quest::router
     * @covers oscarpalmer\Quest\Quest::run
     */
    public function testErrorRun()
    {
        $quest = new Quest([], new Request($this->simple));
        $quest->run();

        $this->expectOutputString("404 Not Found");
    }

    /**
     * @covers oscarpalmer\Quest\Quest::after
     * @covers oscarpalmer\Quest\Quest::before
     * @covers oscarpalmer\Quest\Quest::router
     */
    public function testFilters()
    {
        $quest = new Quest($this->routes, new Request($this->regex));

        $quest->after("*", function () { return " after 1"; });
        $quest->before("*", function () { return "before 1 "; });

        $quest->after("/a/b.c", function () { return " after 2"; });
        $quest->before("/a/b.c", function () { return "before 2 "; });

        $quest->run();

        $this->expectOutputString("before 1 before 2 a/b.c after 1 after 2");
    }

    public function testHalt()
    {
        $quest = new Quest([], new Request($this->simple));

        $quest->get("/", function ($quest) {
            $quest->halt(406);
        });

        $quest->run();

        $this->expectOutputString("406 Not Acceptable");
    }

    public function testHaltCustom()
    {
        $quest = new Quest([], new Request($this->simple));

        $quest->get("/", function ($quest) {
            $quest->halt(406, "Boo!");
        });

        $quest->run();

        $this->expectOutputString("Boo!");
    }

    public function testHeaders()
    {
        $quest = new Quest([], new Request($this->simple));

        $quest->header("x-powered-by", "Quest!");
        $this->assertSame($quest->header("x-powered-by"), "Quest!");

        $quest->contentType("special/quest");
        $this->assertSame($quest->contentType(), "special/quest");
    }

    public function testRedirect()
    {
        $quest = new Quest([], new Request($this->simple));

        try {
            $quest->redirect("/a/b.c");
        } catch (Halt $e) {
            $this->assertNotNull($e);
            $this->assertInstanceOf("oscarpalmer\Quest\Exception\Halt", $e);

            $this->assertSame("302 Found", $e->getMessage());
        }
    }

    /**
     * @covers oscarpalmer\Quest\Quest::pathToRegex
     * @covers oscarpalmer\Quest\Quest::router
     * @covers oscarpalmer\Quest\Quest::run
     */
    public function testRegexRun()
    {
        $quest = new Quest($this->routes, new Request($this->regex));
        $quest->run();

        $this->expectOutputString("a/b.c");
    }

    /**
     * @covers oscarpalmer\Quest\Quest::router
     * @covers oscarpalmer\Quest\Quest::run
     */
    public function testSimpleRun()
    {
        $quest = new Quest($this->routes, new Request($this->simple));
        $quest->run();

        $this->expectOutputString("index");
    }

    /**
     * @covers oscarpalmer\Quest\Quest::setParameter
     * @covers oscarpalmer\Quest\Quest::setParameters
     */
    public function testSetParameters()
    {
        $quest = new Quest([], new Request(["REQUEST_URI" => "/splat/file"]));

        $quest->get("/*/:file(.:ext)", function ($x, $y, $z) {
            $splat = $z->parameters->splat[0];
            $file  = $z->parameters->file;

            echo($z->parameters->file);

            return "{$file} found in {$splat}";
        });

        $quest->run();

        $this->expectOutputString("file found in splat");
    }
}
