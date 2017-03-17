<?php

namespace Arc\Config;

class WPOptions
{
    protected $testConfig = [];

    public function get($key)
    {
        if (isset($this->testConfig[$key])) {
            return $this->testConfig[$key];
        }

        return get_option($key);
    }

    public function isAlreadySet($key)
    {
        return !empty($this->get($key));
    }

    public function setDefault($key, $value)
    {
        if ($this->isAlreadySet($key)) {
            return;
        }
        return $this->set($key, $value);
    }

    public function set($key, $value)
    {
        return add_option($key, $value);
    }

    public function setTest($key, $value)
    {
        $this->testConfig[$key] = $value;
    }
}
