<?php

namespace Arc\Config;

use Arc\Application;
use Arc\Hooks\Filters;

class WPOptions
{
    public function __construct(Application $app, Filters $filters)
    {
        $this->app = $app;
        $this->filters = $filters;
    }

    protected $testConfig = [];

    public function get($key)
    {
        if (isset($this->testConfig[$key])) {
            return $this->testConfig[$key];
        }

        if (!defined('get_option')) {
            return;
        }

        return get_option($key);
    }

    public function isAlreadySet($key)
    {
        return !empty($this->get($key));
    }

    public function exists($key)
    {
        return $this->get($key) !== false;
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
        if ($this->exists($key)) {
            return update_option($key, $value);
        }

        return add_option($key, $value);
    }

    public function setTest($key, $value)
    {
        $this->testConfig[$key] = $value;
    }

    /**
     * Sets the default sending address for wordpress emails.
     *
     * @param string $email
     * @param string $name  (optional)
     **/
    public function setDefaultFromAddress($email, $name = null)
    {
        $this->filters->forHook('wp_mail_from')->doThis(function () use ($email) {
            return $email;
        });
        $this->filters->forHook('wp_mail_from_name')->doThis(function () use ($name) {
            return $name;
        });
    }

    /**
     * Returns true if a from address has been set for outgoing mail.
     *
     * @return bool
     **/
    public function defaultFromAddressIsSet()
    {
        return !empty($this->getDefaultFromAddress());
    }

    /**
     * Returns the default from address for outgoing mail if it is set.
     **/
    public function getDefaultFromAddress()
    {
        return $this->filters->apply('wp_mail_from', '');
    }
}
