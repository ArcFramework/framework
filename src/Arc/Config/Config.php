<?php

namespace Arc\Config;

use Arc\BasePlugin;

class Config
{
    protected $plugin;
    protected $testConfig;

    public function __construct(BasePlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function useTestConfig($testConfig)
    {
        $this->testConfig = $testConfig;
    }

    public function get($key)
    {
        // If a test config value has been set for the given key we'll use that
        if (isset($this->testConfig[$key])) {
            return $this->testConfig[$key];
        }

        $configPath = $this->plugin->path . 'config/app.php';
        $configValues = (file_exists($configPath)) ? include($configPath) : [];

        if (!isset($configValues[$key])) {
            throw new \Exception('No config value for key: "' . $key . '" exists');
        }

        return $configValues[$key];
    }
}
