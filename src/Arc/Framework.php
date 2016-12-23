<?php

namespace Arc;

use Illuminate\Container\Container;

use Arc\Contracts\Activator;
use Arc\Contracts\Deactivator;
use Arc\Contracts\Plugin;

class Framework
{
    private $container;

    private $pluginFileName;

    public $plugin;

    public function __construct($pluginClassName, $pluginFileName)
    {
        // Save the plugin file name attribute
        $this->pluginFileName = $pluginFileName;

        // Instantiate the IoC container
        $this->container = new Container();

        // Resolve the plugin class to a concretion
        $this->plugin = $this->container->make($pluginClassName);

        // Boot the plugin
        $this->boot($this->plugin);
    }

    public function boot()
    {
        $this->registerBindings();

        register_activation_hook($this->pluginFileName, [
            $this->make(Activator::class),
            'activate'
        ]);

        register_deactivation_hook($this->pluginFileName, [
            $this->make(Deactivator::class),
            'activate'
        ]);
    }

    public function make($className)
    {
        return $this->container->make($className);
    }

    /**
     * Registers the service container bindings of concretions to interfaces
     **/
    private function registerBindings()
    {
        $this->container->bind(Activator::class, get_class($this->plugin->getActivator()));
        $this->container->bind(Deactivator::class, get_class($this->plugin->getActivator()));
    }
}