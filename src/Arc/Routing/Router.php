<?php

namespace Arc\Routing;

use Arc\BasePlugin;
use Arc\Http\Controllers\ControllerHandler;
use Arc\Config\FlatFileParser;

class Router
{
    private $parser;
    private $controllerHandler;
    private $routes;

    public function __construct(
        FlatFileParser $parser,
        ControllerHandler $controllerHandler,
        Routes $routes
    )
    {
        $this->parser = $parser;
        $this->controllerHandler = $controllerHandler;
        $this->routes = $routes;
    }

    /**
     * Boot the router and load the routes
     **/
    public function boot()
    {
        // Add the action to parse routes at the appropriate time
        add_action('parse_request', [$this, 'parse']);

        $this->loadRoutes();
    }

    /**
     * Parse the current route to see if it matches any registered routes
     **/
    public function parse()
    {
        // Find the first matching route
        $matchedRoute = $this->routes->match($this->getHTTPVerb(), $this->getURI());

        // If no matching route is found we can return early
        if (is_null($matchedRoute)) {
            return;
        }

        // Execute route callback
        $this->execute($matchedRoute);
    }

    public function getHTTPVerb()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function get($uri, $callback)
    {
        $this->routes->addRoute('get', $uri, $callback);
    }

    public function post($uri, $callback)
    {
        $this->routes->addRoute('post', $uri, $callback);
    }

    private function loadRoutes()
    {
        $this->parser->parseDirectory('routes', [
            'router' => $this
        ]);
    }

    /**
     * Returns the URI of the current page
     *
     * @return string
     **/
    private function getURI()
    {
        return explode('?', $_SERVER['REQUEST_URI'])[0] ?? $_SERVER['PHP_SELF'];
    }

    /**
     * Executes the given route
     **/
    public function execute(Route $route)
    {
        if (is_string($route->callback)) {
            $this->controllerHandler->parseControllerCall($route->callback);
        }

        if (is_callable($route->callback)) {
            call_user_func($route->callback);
        }
    }
}
