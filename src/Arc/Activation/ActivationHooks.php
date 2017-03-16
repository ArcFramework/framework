<?php

namespace Arc\Activation;

use Arc\Application;

class ActivationHooks
{
    /**
     * Register an activation hook with WordPress to Execute the callable when the plugin
     * is activated
     **/
    public function whenPluginIsActivated($callable)
    {
        register_activation_hook(
            config('plugin_filename'),
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
            config('plugin_filename'),
            $callable
        );
    }
}
