<?php

namespace oscarpalmer\Quest;

use oscarpalmer\Shelf\Request;
use oscarpalmer\Shelf\Response;

/**
 * Main Quest class.
 */
class Quest
{
    /**
     * @var string Current version number.
     */
    const VERSION = "0.1.0";

    /**
     * @var Request Shelf Request object.
     */
    protected $request;

    /**
     * @var Response Shelf Response object.
     */
    protected $response;

    /**
     * @var array Array of routes.
     */
    protected $routes;

    /**
     * Create a new Quest object from array of routes and Shelf objects.
     *
     * @param array    $routes   Array of routes.
     * @param Request  $request  Shelf Request object.
     * @param Response $response Shelf Response object.
     */
    public function __construct(
        array $routes = array(),
        Request $request = null,
        Response $response = null
    ) {
        $this->routes = $routes;

        $this->request = $request ?: Request::fromGlobals();
        $this->response = $response ?: new Response;
    }

    /**
     * Magical get functions for properties.
     *
     * @param string $key Key to find.
     * @return mixed Found value for property.
     */
    public function __get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        return null;
    }

    /** Public functions. */

    /**
     * Add a DELETE route.
     *
     * @param string   $path     Path for route.
     * @param callable $callback Callback for route.
     */
    public function delete($path, $callback)
    {
        $this->addRoute("DELETE", $path, $callback);
    }

    /**
     * Add a GET (and HEAD) route.
     *
     * @param string   $path     Path for route.
     * @param callable $callback Callback for route.
     */
    public function get($path, $callback)
    {
        $this->addRoute(array("GET", "HEAD"), $path, $callback);
    }

    /**
     * Add a POST route.
     *
     * @param string   $path     Path for route.
     * @param callable $callback Callback for route.
     */
    public function post($path, $callback)
    {
        $this->addRoute("POST", $path, $callback);
    }

    /**
     * Add a PUT route.
     *
     * @param string   $path     Path for route.
     * @param callable $callback Callback for route.
     */
    public function put($path, $callback)
    {
        $this->addRoute("PUT", $path, $callback);
    }

    /**
     *
     */
    public function run()
    {
        $this->callback();
        $this->response->finish();
    }

    /** Protected functions. */

    /**
     * Add a new Route object to the routes array.
     *
     * @param mixed    $method   Request method for route.
     * @param string   $path     Path for route.
     * @param callable $callback Callback for route.
     */
    protected function addRoute($method, $path, $callback)
    {
        $this->routes[] = new Route((array) $method, $path, $callback);
    }

    /**
     * Run the router and write the halted message to the response body.
     */
    protected function callback()
    {
        ob_start();

        try {
            $this->router();
        } catch (Exception\Halt $exception) {
            $this->response->write($exception->getMessage());
        }

        ob_end_clean();
    }

    /**
     * Loop the routes and run the callback for the first match, or serve an error response.
     */
    protected function router()
    {
        $method = $this->request->request_method;
        $path = $this->request->path_info;

        foreach ($this->routes as $route) {
            if (in_array($method, $route->methods)) {
                $regex = static::pathToRegex($route->path);

                if (preg_match($regex, $path, $keys)) {
                    array_shift($keys);

                    $returned = call_user_func_array($route->callback, $keys);

                    throw new Exception\Halt($returned);
                }
            }
        }

        $this->response->setStatus($method === "GET" ? 404 : 405);

        throw new Exception\Halt($this->response->getStatusMessage());
    }

    /** Static functions. */

    /**
     * Convert path to regex.
     *
     * @param  string $path Path to convert.
     * @return string Regex for path.
     */
    protected static function pathToRegex($path)
    {
        $pattern = array("/\A\/*/", "/\/*\z/", "/\//", "/\./", "/\((.*?)\)/", "/\*/", "/\:(\w+)/");
        $replace = array("/", "/?", "\/", "\.", "(?:\\1)?", "(.*?)", "(\w+)");

        return "/\A" . preg_replace($pattern, $replace, $path) . "\z/";
    }
}
