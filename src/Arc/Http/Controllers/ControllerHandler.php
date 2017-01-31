<?php

namespace Arc\Http\Controllers;

use Arc\Application;

class ControllerHandler
{
    public $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Calls the given method on the given controller
     *
     * @param string $className The short name of the controller class
     * @param string $methodName The name of the controller method
     */
    public function call($className, $methodName)
    {
        $fullyQualifiedClassName = config('plugin_namespace') . '\\Http\\Controllers\\' . $className;

        return app($fullyQualifiedClassName)->$methodName();
    }

    /**
     * Parses a ControllerName@Method string call and calls the relevant controller
     *
     * @param string $call
     **/
    public function parseControllerCall($call)
    {
        $callback = explode('@', $call);

        return $this->call($callback[0], $callback[1]);
    }
}
