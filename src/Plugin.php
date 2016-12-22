<?php

namespace Arc;

abstract class Plugin implements PluginInterface
{
    /**
     * The callable which handles the plugin's activation
     * @var Callable
     **/
    private $activationHandler;

    /**
     * The callable which handles the plugin's deactivation
     * @var Callable
     **/
    private $deactivationHandler;

    /**
     * Returns the handler for activating the plugin
     * @param Callable
     **/
    public function getActivationHandler()
    {
        return $this->activationHandler;
    }

    /**
     * Set the handler for activating the plugin
     * @param Callable $handler
     **/
    public function setActivationHandler(Callable $handler)
    {
        $this->activationHandler = $handler;
    }

    /**
     * Returns the handler for deactivating the plugin
     * @param Callable
     **/
    public function getDeactivationHandler()
    {
        return $this->deactivationHandler;
    }

    /**
     * Set the handler for deactivating the plugin
     * @param Callable $handler
     **/
    public function setDeactivationHandler(Callable $handler)
    {
        $this->handler = $handler;
    }
}
