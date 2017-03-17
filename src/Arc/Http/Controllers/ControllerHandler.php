<?php

namespace Arc\Http\Controllers;

use Arc\Application;
use Arc\Exceptions\ValidationException;

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
    public function call($className, $methodName, $argument = null)
    {
        $fullyQualifiedClassName = config('plugin_namespace') . '\\Http\\Controllers\\' . $className;

        // If we're in ajax mode we need to cache the output
        if (defined('DOING_AJAX') && DOING_AJAX) {
            ob_start();
        }

        try {
            $response = app($fullyQualifiedClassName)->$methodName($argument);
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
