<?php

namespace Arc\Routing;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router as IlluminateRouter;

class Router extends IlluminateRouter
{
    /**
     * Create a new Router instance.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param \Illuminate\Container\Container         $container
     *
     * @return void
     */
    public function __construct(Container $container = null)
    {
        $this->routes = new RouteCollection();
        $this->container = $container ?: new Container();
    }

    /**
     * Dispatch the request to a route and return the response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function dispatchToRoute(Request $request)
    {
        // First we will find a route that matches this request. We will also set the
        // route resolver on the request so middlewares assigned to the route will
        // receive access to this route instance for checking of the parameters.
        $route = $this->findRoute($request);

        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $response = $this->runRouteWithinStack($route, $request);

        return $this->prepareResponse($request, $response);
    }
}
