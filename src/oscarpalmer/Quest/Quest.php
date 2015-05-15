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
    const VERSION = "1.3.0";

    /**
     * @var array Array of route patterns.
     */
    protected $patterns = array("/\A\/*/", "/\/*\z/", "/\//", "/\./", "/\((.*?)\)/", "/\*/", "/\:(\w+)/");

    /**
     * @var array Array of route replacements for patterns.
     */
    protected $replacements = array("/", "/?", "\/", "\.", "(?:\\1)?", "(.*?)", "(\w+)");

    /**
     * @var array Array of error callbacks.
     */
    protected $errors;

    /**
     * @var array Array of filters.
     */
    protected $filters;

    /**
     * @var array Array of various parameters.
     */
    protected $params;

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
        $this->filters = array("after" => array(), "before" => array());
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
     * @param  mixed $path     Path for filter; callback if no path is supplied.
     * @param  mixed $callback Callback for filter; null if no path is supplied.
     * @return Quest Quest object for optional chaining.
     */
    public function after($path, $callback = null)
    {
        $this->addFilter("after", $path, $callback);

        return $this;
    }

    /**
     * Add a filter to run before routing.
     *
     * @param  mixed $path     Path for filter; callback if no path is supplied.
     * @param  mixed $callback Callback for filter; null if no path is supplied.
     * @return Quest Quest object for optional chaining.
     */
    public function before($path, $callback = null)
    {
        $this->addFilter("before", $path, $callback);

        return $this;
    }

    /**
     * Get or set the content type.
     *
     * @param  null|string  $value Content type; null if getting the content type.
     * @return Quest|string Content type as a string or Quest if content type was set.
     */
    public function contentType($value = null)
    {
        if (is_null($value)) {
            return $this->response->getHeader("content-type");
        }

        $this->response->setHeader("content-type", $value);

        return $this;
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
     * @param  callable|int|null $status   Status code or callback for error.
     * @param  callable          $callback Callback for error.
     * @return Quest             Quest object for optional chaining.
     */
    public function error($status = null, $callback = null)
    {
        if (is_null($status) === false) {
            if (is_callable($status)) {
                $this->errors["*"] = $status;

                return $this;
            }

            if (is_int($status)) {
                if (is_null($callback) === false) {
                    if (is_callable($callback)) {
                        $this->errors[$status] = $callback;

                        return $this;
                    }

                    throw new \InvalidArgumentException(
                        "Callback must be a callable, \"" .
                        gettype($callback) .
                        "\" given."
                    );
                }

                return $this->errorCallback($status);
            }

            throw new \InvalidArgumentException(
                "Status must be a callable or an integer, \"" .
                gettype($status) .
                "\" given."
            );
        }

        return $this->errorCallback();
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
     * Halt the response with a status code and display an optional message.
     *
     * @param int         $status  Status code for halted response.
     * @param null|scalar $message Message to display.
     */
    public function halt($status, $message = null)
    {
        if (is_null($message)) {
            return $this->errorCallback($status);
        }

        $this->response->setStatus($status);

        throw new Exception\Halt($message);
    }

    /**
     * Get or set a header.
     *
     * @param  string       $header Header name.
     * @param  null|string  $value  Value for header; null if getting a header.
     * @return Quest|string Value for header or Quest if header was set.
     */
    public function header($header, $value = null)
    {
        if (is_null($value)) {
            return $this->response->getHeader($header);
        }

        $this->response->setHeader($header, $value);

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
     * Redirection.
     *
     * @param string Where to end up.
     * @param int    Valid status code for redirection.
     */
    public function redirect($location, $status = 302)
    {
        if (is_string($location)) {
            $this->response->setHeader("location", $location);

            return $this->halt($status);
        }

        throw new \InvalidArgumentException(
            "Location must be a string, \"" .
            gettype($location) .
            "\" given."
        );
    }

    /**
     * Run the filter and route callbacks and finish the response.
     */
    public function run()
    {
        try {
            ob_start();

            $this->router($this->filters["before"]);
            $this->router($this->routes, true);
        } catch (Exception\Halt $exception) {
            $this->response->write($exception->getMessage());
            $this->router($this->filters["after"]);

            ob_end_clean();
        }

        $this->response->finish($this->request);
    }

    /** Protected functions. */

    /**
     * Add a filter with an optional path.
     *
     * @param string $type     Type of filter.
     * @param mixed  $path     Path for filter; callback if no path is supplied.
     * @param mixed  $callback Callback for filter; null if no path is supplied.
     */
    protected function addFilter($type, $path, $callback)
    {
        if (is_null($callback)) {
            $callback = $path;
            $path = "*";
        }

        $this->filters[$type][] = new Item(array(), $path, $callback);
    }

    /**
     * Add a new Item object to the routes array.
     *
     * @param mixed    $method   Request method for route.
     * @param string   $path     Path for route.
     * @param callable $callback Callback for route.
     */
    protected function addRoute($method, $path, $callback)
    {
        $this->routes[] = new Item((array) $method, $path, $callback);
    }

    /**
     * Run a user defined or default error callback.
     *
     * @param int $status Status code for error.
     */
    protected function errorCallback($status = 500)
    {
        $this->response->setStatus($status);

        if (isset($this->errors[$status])) {
            throw new Exception\Halt(call_user_func($this->errors[$status], $this));
        } elseif (isset($this->errors["*"])) {
            throw new Exception\Halt(call_user_func($this->errors["*"], $this));
        } else {
            throw new Exception\Halt($this->response->getStatusMessage());
        }
    }

    /**
     * Convert path to regex.
     *
     * @param  string $path Path to convert.
     * @return string Regex for path.
     */
    protected function pathToRegex($path)
    {
        return "/\A" . preg_replace($this->patterns, $this->replacements, $path) . "\z/";
    }

    /**
     * Loop the routes and run the callback for the first match, or serve an error response.
     */
    protected function router($container, $routes = false)
    {
        $method = $this->request->request_method;
        $path = $this->request->path_info;

        foreach ($container as $item) {
            if ($routes === false || in_array($method, $item->methods)) {
                $regex = $this->pathToRegex($item->path);

                if (preg_match($regex, $path, $params)) {
                    $params = $this->setParameters($item->path, $regex, $params);

                    $returned = call_user_func_array($item->callback, $params);

                    if ($routes) {
                        throw new Exception\Halt($returned);
                    } else {
                        $this->response->write($returned);
                    }
                }
            }
        }

        if ($routes) {
            return $this->errorCallback($method === "GET" ? 404 : 405);
        }
    }

    /**
     * Set global parameters based on route path, route regex, and route parameters.
     */
    protected function setParameters($route, $regex, $values)
    {
        array_shift($values);

        preg_match_all("/(\:\w+|\*)/", $route, $keys);

        foreach ($keys[0] as $index => $key) {
            $key = ltrim($key, ":");
            $val = isset($values[$index]) ? $values[$index] : null;

            if (is_null($val)) {
                continue;
            }

            if ($key === "*") {
                $this->params->splat[] = $val;
            } else {
                $this->params->$key = $val;
            }
        }

        $values[] = $this;

        return $values;
    }
}
