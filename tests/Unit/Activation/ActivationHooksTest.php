<?php

use Arc\Activation\ActivationHooks;
use Arc\Config\Config;

class ActivationHooksTest extends FrameworkTestCase
{
    /** @test */
    public function an_activation_hook_can_be_registered()
    {
        $doThis = ['object' => 'method'];

        WP_Mock::wpFunction('register_activation_hook', [
            'times' => 1,
            'args' => [
                $this->plugin->filename,
                $doThis
            ]
        ]);

        $this->plugin->make(ActivationHooks::class)->whenPluginIsActivated($doThis);
    }

    /** @test */
    public function a_deactivation_hook_can_be_registered()
    {
        $doThis = ['object' => 'method'];

        WP_Mock::wpFunction('register_deactivation_hook', [
            'times' => 1,
            'args' => [
                $this->plugin->filename,
                $doThis
            ]
        ]);

        $this->plugin->make(ActivationHooks::class)->whenPluginIsDeactivated($doThis);
    }
}
