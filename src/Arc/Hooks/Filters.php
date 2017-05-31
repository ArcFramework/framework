<?php

namespace Arc\Hooks;

interface Filters
{
    /**
     * Set the hook for the action
     * string $hook.
     **/
    public function forHook($hook);

    public function apply($hook, $text, ...$args);

    public function add($slug, $callable);
}

