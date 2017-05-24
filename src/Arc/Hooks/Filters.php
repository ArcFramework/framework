<?php

namespace Arc\Hooks;

class Filters
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
     * Apply the filters for the given hook on the given text and return the result.
     *
     * @param string $hook
     * @param string $text
     * @params $args (optional) Optional additional parameters to pass into the callbacks
     *
     * @return mixed
     **/
    public function apply($hook, $text, ...$args)
    {
        return apply_filters($hook, $text, ...$args);
    }

    /**
     * Set the callable to be called when the action is invoked and register the action
     * in WordPress
     * Callable $callable.
     **/
    public function doThis($callable)
    {
        add_filter($this->hook, $callable);
    }
}
