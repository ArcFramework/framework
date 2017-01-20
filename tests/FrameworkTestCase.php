<?php

abstract class FrameworkTestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \WP_Mock::setUp();

        $this->plugin = new Arc\BasePlugin(__FILE__);
        $this->app = $this->plugin->app;
    }

    public function tearDown()
    {
        \WP_Mock::tearDown();
    }
}
