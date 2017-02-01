<?php

namespace Arc\Config;

class Config
{
    public function get($key)
    {
        $pluginPath = env('PLUGIN_PATH', ABSPATH . 'wp-content/plugins/' . env('PLUGIN_SLUG') . '/');

        $configValues = include($pluginPath . 'config/app.php');

        if (!isset($configValues[$key])) {
            throw new \Exception('No config value for key: "' . $key . '" exists');
        }

        return $configValues[$key];
    }
}
