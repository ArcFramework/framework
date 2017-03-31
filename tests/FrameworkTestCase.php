<?php

use Arc\Testing\Concerns\InteractsWithPages;
use Arc\Testing\Concerns\MakesHttpRequests;

abstract class FrameworkTestCase extends PHPUnit_Framework_TestCase
{
    use MakesHttpRequests;

    public static $functions;

    public $baseUrl = 'http://localhost';

    public function setUp()
    {
        WP_Mock::setUp();

        self::$functions = Mockery::mock();

        $this->app = new TestPlugin(__FILE__);
    }

    public function tearDown()
    {
        WP_Mock::tearDown();
    }
}
