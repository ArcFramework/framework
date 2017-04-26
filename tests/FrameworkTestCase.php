<?php

use Arc\Http\Kernel;
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

        $this->app = new TestPlugin(realpath(__DIR__.'/test-plugin/test-plugin.php'));
        $this->app->make(Kernel::class)->bootstrap();
    }

    public function tearDown()
    {
        WP_Mock::tearDown();
    }
}
