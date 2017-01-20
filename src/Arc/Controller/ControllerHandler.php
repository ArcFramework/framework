<?php

namespace Arc\Controller;

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
     * @param string $controllerName The short name of the controller class
     * @param string $controllerMethod the name of the controller method
     */
    public function call($controllerName, $controllerMethod)
    {
        $fullyQualifiedClassName = $this->app->pluginNamespace . '\\Controllers\\' . $controllerName;

        $controller = new $fullyQualifiedClassName($this);

        return $controller->$method();
    }
}
