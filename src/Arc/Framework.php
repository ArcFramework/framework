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

    public function __construct($pluginFileName)
    {
        // Save the plugin file name attribute
        $this->pluginFileName = $pluginFileName;

        // Instantiate the IoC container
        $this->container = new Container();
    }

    public function boot($pluginClassName)
    {
        // Bind plugin class
        //$this->container->bind($pluginClassName, Arc\BasePlugin);

        // Get all other bindings
        $this->registerBindings();

        // Resolve the plugin class to a concretion
        $this->plugin = $this->container->make($pluginClassName);


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
