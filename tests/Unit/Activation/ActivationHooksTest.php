<?php

use Arc\Activation\ActivationHooks;
use Arc\Config\Config;

class ActivationHooksTest extends FrameworkTestCase
{
    /** @test */
    public function an_activation_hook_can_be_registered()
    {
        app('configuration')->useTestConfig([
            'plugin_filename' => 'test-plugin-filename.php'
        ]);

        $doThis = ['object' => 'method'];

        WP_Mock::wpFunction('register_activation_hook', [
            'times' => 1,
            'args' => [
                'test-plugin-filename.php',
                $doThis
            ]
        ]);

        app(ActivationHooks::class)->whenPluginIsActivated($doThis);
    }

    /** @test */
    public function a_deactivation_hook_can_be_registered()
    {
        app('configuration')->useTestConfig([
            'plugin_filename' => 'test-plugin-filename.php'
        ]);

        $doThis = ['object' => 'method'];

        WP_Mock::wpFunction('register_deactivation_hook', [
            'times' => 1,
            'args' => [
                'test-plugin-filename.php',
                $doThis
            ]
        ]);

        app(ActivationHooks::class)->whenPluginIsDeactivated($doThis);
    }
}
