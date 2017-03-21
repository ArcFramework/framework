<?php

abstract class FrameworkTestCase extends PHPUnit_Framework_TestCase
{
    public static $functions;

    public function setUp()
    {
        WP_Mock::setUp();

        self::$functions = Mockery::mock();

        $this->plugin = new TestPlugin(__FILE__);
    }

    public function tearDown()
    {
        WP_Mock::tearDown();
    }
}
