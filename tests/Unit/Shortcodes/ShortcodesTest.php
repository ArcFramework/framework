<?php

use Arc\Shortcodes\Shortcodes;

class ShortcodesTest extends FrameworkTestCase
{
    /** @test */
    public function the_class_can_register_a_shortcode_via_the_fluent_api()
    {
        WP_Mock::wpFunction('add_shortcode', [
            'times' => 1,
        ]);

        $this->app->make(Shortcodes::class)
            ->code('test-shortcode')
            ->rendersView('test', [
                'variable' => true
            ])
            ->register();
    }

    /** @test */
    public function the_class_can_render_a_shortcode()
    {
        $shortcodes = $this->app->make(Shortcodes::class);

        $shortcodes->code('test-shortcode')
            ->rendersView('test', [
                'variable' => true
            ]);

        $shortcodes->render(null, '', 'test-shortcode');
    }
}
