<?php

use Arc\Hooks\Activation;

class ActivationHooksTest extends FrameworkTestCase
{
    /**
     * @test
     *
     * Feature documented at arcframework.github.io/hooks.html
     **/
    public function an_activation_hook_can_be_registered()
    {
        $doThis = ['object' => 'method'];

        WP_Mock::wpFunction('register_activation_hook', [
            'times' => 1,
            'args'  => [
                $this->app->filename,
                $doThis,
            ],
        ]);

        $this->app->make(Activation::class)->whenPluginIsActivated($doThis);
    }

    /**
     * @test
     *
     * Feature documented at arcframework.github.io/hooks.html
     **/
    public function a_deactivation_hook_can_be_registered()
    {
        $doThis = ['object' => 'method'];

        WP_Mock::wpFunction('register_deactivation_hook', [
            'times' => 1,
            'args'  => [
                $this->app->filename,
                $doThis,
            ],
        ]);

        $this->app->make(Activation::class)->whenPluginIsDeactivated($doThis);
    }
}
