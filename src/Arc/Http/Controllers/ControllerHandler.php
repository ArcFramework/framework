<?php

namespace Arc\Http\Controllers;

use Arc\Application;
use Arc\Exceptions\ValidationException;

class ControllerHandler
{
    protected $plugin;

    public function __construct(Application $plugin)
    {
        $this->app = $plugin;
    }

    /**
     * Calls the given method on the given controller
     *
     * @param string $className The short name of the controller class
     * @param string $methodName The name of the controller method
     */
    public function call($classAndMethod, $arguments = null)
    {
        if (is_object($classAndMethod[0])) {
            $classAndMethod[0] = get_class($classAndMethod[0]);
        }

        if (class_exists($classAndMethod[0])) {
            $className = $classAndMethod[0];
        }
        else {
            // Try fully qualified class name
            $className = $this->app->namespace . '\\Http\\Controllers\\' . $classAndMethod[0];
        }

        $methodName = $classAndMethod[1];

        // If we're in ajax mode we need to cache the output
        if (defined('DOING_AJAX') && DOING_AJAX) {
            ob_start();
        }

        try {
            $controller = $this->app->make($fullyQualifiedClassName);

            // We do it this way to avoid having to inject the plugin into every controller
            // or call the parent constructor in every controller
            $controller->setPluginInstance($this->app);

            $response = $this->app->call([$controller, $methodName], [$argument]);
        }
        catch (ValidationException $e) {
            wp_send_json([
                'success' => false,
                'messages' => $e->errors()
            ]);
        }
        catch (\Exception $e) {
            wp_send_json_error([$e->getMessage()], $e->getCode());
            throw new \Exception($e);
        }

        // If we're in ajax mode we need to collect the cached output
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return ob_get_clean();
        }

        return $response;
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
