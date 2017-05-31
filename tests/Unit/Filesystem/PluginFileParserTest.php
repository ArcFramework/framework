<?php

use Arc\Filesystem\PluginFileParser;

class PluginFileParserTest extends FrameworkTestCase
{
    /** @test */
    public function the_get_plugin_version_method_returns_the_version_when_given_a_valid_plugin_file()
    {
        $pluginFilename = realpath(__DIR__.'/../../test-plugin/test-plugin.php');

        $this->assertEquals(
            '0.0.0',
            $this->app->make(PluginFileParser::class)->getPluginVersion($pluginFilename)
        );
    }

    /** @test */
    public function the_get_plugin_name_method_returns_the_title_when_given_a_valid_plugin_file()
    {
        $pluginFilename = realpath(__DIR__.'/../../test-plugin/test-plugin.php');

        $this->assertEquals(
            'Arc Test Plugin',
            $this->app->make(PluginFileParser::class)->getPluginName($pluginFilename)
        );
    }
}
