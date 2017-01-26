<?php

use Arc\Activation\ActivationHooks;

class ActivationHooksTest extends FrameworkTestCase
{
    /** @test */
    public function an_activation_hook_can_be_registered()
    {
        $doThis = ['object' => 'method'];

        WP_Mock::wpFunction('register_activation_hook', [
            'times' => 1,
            'args' => [
                config('plugin_filename'),
                $doThis
            ]
        ]);

        app(ActivationHooks::class)->whenPluginIsActivated($doThis);
    }

    /** @test */
    public function a_deactivation_hook_can_be_registered()
    {
        $doThis = ['object' => 'method'];

        WP_Mock::wpFunction('register_deactivation_hook', [
            'times' => 1,
            'args' => [
                config('plugin_filename'),
                $doThis
            ]
        ]);

        app(ActivationHooks::class)->whenPluginIsDeactivated($doThis);
    }
}
