<?php

namespace Arc;

class Framework
{
    const VERSION = '0.0.0';

    public function __construct()
    {
        $this->version = new SemVersion(self::VERSION);
    }

    public function boot(PluginContract $plugin)
    {
        $this->plugin = $plugin;

        $this->plugin->registerActivationHooks();
        $this->plugin->registerDeactivationHooks();
    }
}
