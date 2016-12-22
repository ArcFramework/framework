<?php

namespace Arc;

interface PluginInterface
{
    /**
     * Returns the handler for activating the plugin
     * @param Callable
     **/
    public function getActivationHandler();

    /**
     * Set the handler for activating the plugin
     * @param Callable
     **/
    public function setActivationHandler(Callable $callable);

    /**
     * Returns the handler for deactivating the plugin
     * @param Callable
     **/
    public function getDeactivationHandler();

    /**
     * Set the handler for deactivating the plugin
     * @param Callable
     **/
    public function setDeactivationHandler(Callable $callable);
}
