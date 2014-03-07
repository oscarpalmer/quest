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
     * @var array Array of error callbacks.
     */
    protected $errors;

    /**
     * @var array Array of filters.
     */
    protected $filters;

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
     * Create a new Quest object from an array of routes and Shelf objects;
     * parameters are optional.
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
        $this->errors = array();
        $this->filters = array();
        $this->routes = $routes;

        $this->request = $request ?: Request::fromGlobals();
        $this->response = $response ?: new Response;
    }

    /**
     * Magical get functions for properties.
     *
     * @param  string $key Key to find.
     * @return mixed  Found value for property.
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
     * Add a filter to run after routing.
     *
     * @param  callable Callback for filter.
     * @return Quest Quest object for optional chaining.
     */
    public function after($callback)
    {
        if (is_callable($callback)) {
            $this->filters["after"] = $callback;

            return $this;
        }

        throw new \InvalidArgumentException("Callback must be a callable, " . gettype($callback) . " given.");
    }

    /**
     * Add a filter to run before routing.
     *
     * @param  callable Callback for filter.
     * @return Quest Quest object for optional chaining.
     */
    public function before($callback)
    {
        if (is_callable($callback)) {
            $this->filters["before"] = $callback;

            return $this;
        }

        throw new \InvalidArgumentException("Callback must be a callable, " . gettype($callback) . " given.");
    }

    /**
     * Add a DELETE route.
     *
     * @param  string   $path     Path for route.
     * @param  callable $callback Callback for route.
     * @return Quest    Quest object for optional chaining.
     */
    public function delete($path, $callback)
    {
        $this->addRoute("DELETE", $path, $callback);

        return $this;
    }

    /**
     * Add or run an error callback.
     *
     * @param  int      $status   Status code for error.
     * @param  callable $callback Callback for error.
     * @return Quest    Quest object for optional chaining.
     */
    public function error($status, $callback = null)
    {
        if (is_int($status) === false) {
            throw new \InvalidArgumentException("Status must be an integer, " . gettype($status) . " given.");
        }

        if (isset($callback)) {
            if (is_callable($callback) === false) {
                throw new \InvalidArgumentException("Callback must be a callable, " . gettype($callback) . " given.");
            }

            $this->errors[$status] = $callback;

            return $this;
        }

        $this->errorCallback($status);
    }

    /**
     * Add a GET (and HEAD) route.
     *
     * @param  string   $path     Path for route.
     * @param  callable $callback Callback for route.
     * @return Quest    Quest object for optional chaining.
     */
    public function get($path, $callback)
    {
        $this->addRoute(array("GET", "HEAD"), $path, $callback);

        return $this;
    }

    /**
     * Add a POST route.
     *
     * @param  string   $path     Path for route.
     * @param  callable $callback Callback for route.
     * @return Quest    Quest object for optional chaining.
     */
    public function post($path, $callback)
    {
        $this->addRoute("POST", $path, $callback);

        return $this;
    }

    /**
     * Add a PUT route.
     *
     * @param  string   $path     Path for route.
     * @param  callable $callback Callback for route.
     * @return Quest    Quest object for optional chaining.
     */
    public function put($path, $callback)
    {
        $this->addRoute("PUT", $path, $callback);

        return $this;
    }

    /**
     * Run the callback and finish the response.
     */
    public function run()
    {
        $this->callback();
        $this->response->finish($this->request);
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
            $this->filter("before");
            $this->router();
        } catch (Exception\Halt $exception) {
            $this->response->write($exception->getMessage());
            $this->filter("after");
        }

        ob_end_clean();
    }

    /**
     * Run a filter callback.
     *
     * @param string $name Name for filter.
     */
    protected function filter($name)
    {
        if (isset($this->filters[$name])) {
            $returned = call_user_func($this->filters[$name], $this);

            $this->response->write($returned);
        }
    }

    /**
     * Run a user defined or default error callback.
     *
     * @param int $status Status code for error.
     */
    protected function errorCallback($status)
    {
        $this->response->setStatus($status);

        if (isset($this->errors[$status])) {
            throw new Exception\Halt(call_user_func($this->errors[$status], $this));
        } else {
            throw new Exception\Halt($this->response->getStatusMessage());
        }
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

                if (preg_match($regex, $path, $params)) {
                    array_shift($params);

                    $params[] = $this;

                    $returned = call_user_func_array($route->callback, $params);

                    throw new Exception\Halt($returned);
                }
            }
        }

        $this->errorCallback($method === "GET" ? 404 : 405);
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
