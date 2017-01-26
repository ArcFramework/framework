<?php

namespace Arc\Config;

class Config
{
    public function get($key)
    {
        $configValues = include(env('PLUGIN_PATH') . 'config/app.php');

        if (!isset($configValues[$key])) {
            throw new \Exception('No config value for key: "' . $key . '" exists');
        }

        return $configValues[$key];
    }
}
