<?php

declare(strict_types = 1);

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
    const VERSION = '2.2.0';

    /**
     * @var array Array of route patterns.
     */
    const ROUTE_PATTERNS = ['/\A\/*/', '/\/*\z/', '/\//', '/\./', '/\((.*?)\)/', '/\*/', '/\:(\w+)/'];

    /**
     * @var array Array of route replacements for patterns.
     */
    const ROUTE_REPLACEMENTS = ['/', '/?', '\/', '\.', '(?:\\1)?', '(.*?)', '(\w+)'];

    /**
     * @var array Array of error callbacks.
     */
    protected $errors = null;

    /**
     * @var array Array of filters.
     */
    protected $filters = null;

    /**
     * @var array Array of various parameters.
     */
    protected $parameters = null;

    /**
     * @var Request Shelf Request object.
     */
    protected $request = null;

    /**
     * @var Response Shelf Response object.
     */
    protected $response = null;

    /**
     * @var array Array of routes.
     */
    protected $routes = null;

    /**
     * Create a new Quest object from an array of routes and Shelf objects;
     * parameters are optional.
     *
     * @param array    $routes   Array of routes.
     * @param Request  $request  Shelf Request object.
     * @param Response $response Shelf Response object.
     */
    public function __construct(
        array $routes = [],
        Request $request = null,
        Response $response = null
    ) {
        $this->errors = [];
        $this->filters = ['after' => [], 'before' => []];
        $this->routes = $routes;

        $this->parameters = new \stdClass;
        $this->parameters->splat = [];

        $this->request = $request ?: Request::fromGlobals();
        $this->response = $response ?: new Response;
    }

    /**
     * Magical get functions for properties.
     *
     * @param  string $key Key to find.
     * @return mixed  Found value for property.
     */
    public function __get(string $key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        return $this->request->$key ?? null;
    }

    /** Public functions. */

    /**
     * Add a filter to run after routing.
     *
     * @param  string   $path     Path for filter.
     * @param  callable $callback Callback for filter.
     * @return Quest    Quest object for optional chaining.
     */
    public function after(string $path, callable $callback) : Quest
    {
        $this->filters['after'][] = new Items\Filter(Request::REQUEST_METHODS, $path, $callback);

        return $this;
    }

    /**
     * Add a filter to run before routing.
     *
     * @param  string   $path     Path for filter.
     * @param  callable $callback Callback for filter.
     * @return Quest    Quest object for optional chaining.
     */
    public function before(string $path, callable $callback) : Quest
    {
        $this->filters['before'][] = new Items\Filter(Request::REQUEST_METHODS, $path, $callback);

        return $this;
    }

    /**
     * Get or set the content type.
     *
     * @param  string       $value Content type; null if getting the content type.
     * @return Quest|string Content type as a string or Quest if content type was set.
     */
    public function contentType(string $value = null)
    {
        if (is_null($value)) {
            return $this->response->getHeader('content-type');
        }

        $this->response->setHeader('content-type', $value);

        return $this;
    }

    /**
     * Add a DELETE route.
     *
     * @param  string   $path     Path for route.
     * @param  callable $callback Callback for route.
     * @return Quest    Quest object for optional chaining.
     */
    public function delete(string $path, callable $callback) : Quest
    {
        $this->routes[] = new Items\Route([Request::REQUEST_METHODS[0]], $path, $callback);

        return $this;
    }

    /**
     * Add or run an error callback.
     *
     * @param  int      $status   Status code or callback for error.
     * @param  callable $callback Callback for error.
     * @return mixed    Quest object for optional chaining.
     */
    public function error(int $status = 500, callable $callback = null)
    {
        if (is_null($callback)) {
            return $this->errorCallback($status);
        }

        $this->errors[$status] = $callback;

        return $this;
    }

    /**
     * Add a GET (and HEAD) route.
     *
     * @param  string   $path     Path for route.
     * @param  callable $callback Callback for route.
     * @return Quest    Quest object for optional chaining.
     */
    public function get(string $path, callable $callback) : Quest
    {
        $this->routes[] = new Items\Route([Request::REQUEST_METHODS[1], Request::REQUEST_METHODS[2]], $path, $callback);

        return $this;
    }

    /**
     * Halt the response with a status code and display an optional message.
     *
     * @param int         $status  Status code for halted response.
     * @param null|scalar $message Message to display.
     */
    public function halt(int $status, $message = null)
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
     * @param  string      $header Header name.
     * @param  mixed       $value  Value for header; null if getting a header.
     * @return Quest|mixed Value for header or Quest if a header was set.
     */
    public function header(string $header, $value = null)
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
    public function post(string $path, callable $callback) : Quest
    {
        $this->routes[] = new Items\Route([Request::REQUEST_METHODS[3]], $path, $callback);

        return $this;
    }

    /**
     * Add a PUT route.
     *
     * @param  string   $path     Path for route.
     * @param  callable $callback Callback for route.
     * @return Quest    Quest object for optional chaining.
     */
    public function put(string $path, callable $callback) : Quest
    {
        $this->routes[] = new Items\Route([Request::REQUEST_METHODS[4]], $path, $callback);

        return $this;
    }

    /**
     * Redirection.
     *
     * @param string Where to end up.
     * @param int    Status code for redirection.
     */
    public function redirect(string $location, int $status = 302)
    {
        $this->response->setHeader('location', $location);

        return $this->halt($status);
    }

    /**
     * Run the filter and route callbacks and finish the response.
     */
    public function run()
    {
        try {
            ob_start();

            $this->router($this->filters['before']);
            $this->router($this->routes, true);
        } catch (Exception\Halt $exception) {
            $this->response->write($exception->getMessage());
            $this->router($this->filters['after']);

            ob_end_clean();
        }

        $this->response->finish($this->request);
    }

    /** Protected functions. */

    /**
     * Run a user defined or default error callback.
     *
     * @param int $status Status code for error.
     */
    protected function errorCallback(int $status = 500)
    {
        $this->response->setStatus($status);

        if (isset($this->errors[$status])) {
            throw new Exception\Halt(call_user_func($this->errors[$status], $this));
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
    protected function pathToRegex(string $path) : string
    {
        return '/\A' . preg_replace(static::ROUTE_PATTERNS, static::ROUTE_REPLACEMENTS, $path) . '\z/';
    }

    /**
     * Loop the routes and run the callback for the first match, or serve an error response.
     *
     * @param array $container Container for routing.
     * @param bool  $routes    True if container contains routes.
     */
    protected function router(array $container, bool $routes = false)
    {
        foreach ($container as $item) {
            if (in_array($this->request->request_method, $item->getMethods())) {
                $regex = $this->pathToRegex($item->getPath());

                if (preg_match($regex, $this->request->path_info, $parameters)) {
                    $parameters = $this->setParameters($item->getPath(), $regex, $parameters);

                    $returned = call_user_func_array($item->getCallback(), $parameters);

                    if ($routes) {
                        throw new Exception\Halt($returned);
                    } else {
                        $this->response->write($returned);
                    }
                }
            }
        }

        if ($routes) {
            $this->errorCallback($this->request->request_method === Request::REQUEST_METHODS[1] ? 404 : 405);
        }
    }

    /**
     * Inspect one specific parameter and set it globally if possible.
     *
     * @param string $route Route for for parameter.
     * @param string $key   Key for parameter.
     * @param string $value Value for parameter.
     */
    protected function setParameter(string $route, string $key, $value)
    {
        $key = ltrim($key, ':');

        # Skip if key matches our route, if value is bad or value already exists in parameters
        if (is_null($value) || $key === $route || in_array($value, $this->parameters->splat)) {
            return;
        }

        if ($key === '*') {
            $this->parameters->splat[] = $value;
        } else {
            $this->parameters->$key = $value;
        }
    }

    /**
     * Set global parameters based on path, regex, and parameters.
     *
     * @param  string $route  Route to base parameters on.
     * @param  string $regex  Regex for route.
     * @param  array  $values Array of route parameters.
     * @return array  Updated array of parameters.
     */
    protected function setParameters(string $route, string $regex, array $values) : array
    {
        array_shift($values);

        preg_match_all('/(\:\w+|\*)/', $route, $keys);

        foreach ($keys[0] as $index => $key) {
            $this->setParameter($route, $key, $values[$index] ?? null);
        }

        $values[] = $this;

        return $values;
    }
}
