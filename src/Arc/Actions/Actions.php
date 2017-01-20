<?php

namespace Arc\Actions;

class Actions
{
    private $hook;

    /**
     * Set the hook for the action
     * string $hook
     **/
    public function forHook($hook)
    {
        $this->hook = $hook;
        return $this;
    }

    /**
     * Set the callable to be called when the action is invoked and register the action
     * in WordPress
     * Callable $callable
     **/
    public function doThis($callable)
    {
        add_action($this->hook, $callable);
    }
}
