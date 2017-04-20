<?php

use Arc\Application;

class TestPlugin extends Application
{
    protected static $instance;

    /**
     * Set the shared instance of the application.
     *
     * @param  Application|null  $container
     * @return static
     */
    public static function setApplicationInstance(Application $application)
    {
        self::$instance = $application;
    }

    /**
     * Get the shared instance of the application.
     *
     * @return static
     */
    public static function app()
    {
        return self::$instance;
    }
}
