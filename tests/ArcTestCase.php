<?php

abstract class ArcTestCase extends WP_UnitTestCase
{
    const PLUGIN_FILE = __DIR__ . '/test-plugin/test-plugin.php';

    public function setUp()
    {
        $this->framework = new Arc\Framework(Arc\TestPlugin\TestPlugin::class, __FILE__);
    }
}
