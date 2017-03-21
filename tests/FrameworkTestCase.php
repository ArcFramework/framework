<?php

abstract class FrameworkTestCase extends PHPUnit_Framework_TestCase
{
    public static $functions;

    public function setUp()
    {
        WP_Mock::setUp();

        self::$functions = Mockery::mock();

        $this->plugin = new Arc\BasePlugin(__FILE__);
        $this->app = $this->plugin->app;
    }

    public function tearDown()
    {
        WP_Mock::tearDown();
    }
}
