<?php

namespace Arc;

use Arc\Contracts\Activator;
use Arc\Contracts\Deactivator;
use Arc\Contracts\Plugin as PluginContract;

abstract class BasePlugin implements PluginContract
{
    /**
     * The callable which handles the plugin's activation
     * @var Callable
     **/
    private $activator;

    /**
     * The callable which handles the plugin's deactivation
     * @var Callable
     **/
    private $deactivator;

    /**
     * Returns the handler for activating the plugin
     * @param Callable
     **/
    public function getActivator()
    {
        return $this->activator;
    }

    /**
     * Set the handler for activating the plugin
     * @param Callable $handler
     **/
    public function setActivator(Activator $activator)
    {
        $this->activator = $activator;
    }

    /**
     * Returns the handler for deactivating the plugin
     * @param Callable
     **/
    public function getDeactivator()
    {
        return $this->deactivator;
    }

    /**
     * Set the handler for deactivating the plugin
     * @param Callable $handler
     **/
    public function setDeactivator(Deactivator $deactivator)
    {
        $this->deactivator = $deactivator;
    }
}
