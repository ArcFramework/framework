<?php

namespace Arc\Activation;

use Arc\Application;

class ActivationHooks
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register an activation hook with WordPress to Execute the callable when the plugin
     * is activated
     **/
    public function whenPluginIsActivated($callable)
    {
        register_activation_hook($this->app->pluginFilename, $callable);
    }

    /**
     * Register a deactivation hook with Wordpress to execture the callable when the plugin
     * is deactivated
     **/
    public function whenPluginIsDeactivated($callable)
    {
        register_deactivation_hook($this->app->pluginFilename, $callable);
    }
}
