<?php

class PluginActivationTest extends ArcTestCase
{
    /** @test */
    function an_activator_and_deactivator_can_be_registered()
    {
        $this->assertTrue(has_action('activate_' . ltrim(self::PLUGIN_FILE, '/')));
        $this->assertTrue(has_action('deactivate_' . ltrim(self::PLUGIN_FILE, '/')));
    }
}
