<?php

namespace Arc\Activation;

use Arc\Application;

class ActivationHooks
{
    protected $app;
    protected $pluginFile;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->pluginFile = preg_replace(
            '#/+#','/', config('plugin_file')
        );
    }

    /**
     * Register an activation hook with WordPress to Execute the callable when the plugin
     * is activated
     **/
    public function whenPluginIsActivated($callable)
    {
        register_activation_hook(
            $this->pluginFile,
            $callable
        );
    }

    /**
     * Register a deactivation hook with Wordpress to execture the callable when the plugin
     * is deactivated
     **/
    public function whenPluginIsDeactivated($callable)
    {
        register_deactivation_hook(
            $this->pluginFile,
            $callable
        );
    }
}
