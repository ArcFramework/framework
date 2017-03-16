<?php

namespace Arc\Config;

class Config
{
    protected $testConfig;

    public function useTestConfig($testConfig)
    {
        $this->testConfig = $testConfig;
    }

    public function get($key)
    {
        $pluginPath = env('PLUGIN_PATH', ABSPATH . 'wp-content/plugins/' . env('PLUGIN_SLUG') . '/');

        // If a test config value has been set for the given key we'll use that
        if (isset($this->testConfig[$key])) {
            return $this->testConfig[$key];
        }

        $configValues = include($pluginPath . 'config/app.php');

        if (!isset($configValues[$key])) {
            throw new \Exception('No config value for key: "' . $key . '" exists');
        }

        return $configValues[$key];
    }
}
