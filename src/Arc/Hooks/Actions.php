<?php

namespace Arc\Hooks;

class Actions
{
    protected $hook;

    /**
     * Set the hook for the action
     * string $hook.
     **/
    public function forHook($hook)
    {
        $this->hook = $hook;

        return $this;
    }

    /**
     * Run the actions for the given hook and return the result.
     *
     * @param string $hook
     *
     * @return mixed
     **/
    public function do($hook)
    {
        return do_action($hook);
    }

    /**
     * Set the callable to be called when the action is invoked and register the action
     * in WordPress
     * Callable $callable.
     **/
    public function doThis($callable)
    {
        add_action($this->hook, $callable);
    }
}
