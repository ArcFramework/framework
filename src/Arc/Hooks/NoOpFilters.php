<?php

namespace Arc\Hooks;

/**
 * This is one lazy-ass class.
 **/
class NoOpFilters extends Filters
{
    /**
     * Set the hook for the action
     * string $hook.
     **/
    public function forHook($hook)
    {
        // No op
    }

    public function apply($hook, $text, ...$args)
    {
        // No op
    }

    public function add($slug, $callable)
    {
        // No op
    }
}
