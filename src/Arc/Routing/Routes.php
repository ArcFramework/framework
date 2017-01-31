<?php

namespace Arc\Routing;

class Routes
{
    private $getRoutes;
    private $postRoutes;

    /**
     * Adds a route to the collection of routes
     *
     * @param string $verb (post/get) The HTTP verb of the route
     * @param string $uri The URI if the route
     * @param Callable $callback The callback to trigger when a request matches the route
     **/
    public function addRoute($verb, $uri, $callback)
    {
        $this->{$verb . 'Routes'}[] = new Route($verb, $uri, $callback);
    }

    public function match($verb, $uri)
    {
        if ($verb === 'POST') {
            return $this->matchPostRoutes($uri);
        }

        if ($verb === 'GET') {
            return $this->matchGetRoutes($uri);
        }

        throw new \Exception('Invalid HTTP Verb: "' . $verb . '"');
    }

    public function matchGetRoutes($uri)
    {
        return $this->matchRoutes($this->getRoutes, $uri);
    }

    public function matchPostRoutes($uri)
    {
        return $this->matchRoutes($this->postRoutes, $uri);
    }

    private function matchRoutes($routes, $uri)
    {
        if (is_null($routes)) {
            return;
        }

        foreach ($routes as $route)
        {
            if ($this->clean($uri) == $this->clean($route->uri)) {
                return $route;
            }
        }
    }

    /**
     * Removes leading and trailing forward slashes from string
     *
     * @return string
     **/
    private function clean($string)
    {
        return rtrim(ltrim($string, '/'), '/');
    }
}
