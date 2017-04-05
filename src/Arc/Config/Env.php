<?php

namespace Arc\Config;

class Env
{
    protected $directory;

    public function load()
    {
        $environment = [];

        if (file_exists($this->directory . '/.env')) {
            foreach(file($this->directory . '/.env') as $envLine) {
                $pair = explode('=', $envLine);
                $environment[trim($pair[0])] = trim($pair[1]);
            }
        }

        return $environment;
    }

    public function get($key, $default = null)
    {
        $environment = $this->load();

        if (isset($environment[$key])) {
            return $environment[$key];
        }

        return $default;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }
}
