<?php

use Arc\Config\WPOptions;

class WPOptionsTest extends FrameworkTestCase
{
    /** @test */
    public function the_get_method_calls_the_get_option_function()
    {
        WP_Mock::wpFunction('get_option', [
            'times' => 1,
            'args' => [
                'key',
            ]
        ]);

        (new WPOptions)->get('key');
    }

    /** @test */
    public function the_set_method_calls_the_set_option_function()
    {
        WP_Mock::wpFunction('add_option', [
            'times' => 1,
            'args' => [
                'key',
                'value',
            ]
        ]);

        (new WPOptions)->set('key', 'value');
    }

    /** @test */
    public function the_set_test_method_sets_a_test_value_without_touching_wordpress_api()
    {
        WP_Mock::wpFunction('add_option', [
            'times' => 0,
        ]);

        $wpOptions = new WPOptions;
        $wpOptions->setTest('key', 'value');

        $this->assertEquals('value', $wpOptions->get('key'));
    }

    /** @test */
    public function the_set_default_method_sets_a_config_value_if_none_has_already_been_set()
    {
        WP_Mock::wpFunction('add_option', [
            'times' => 1,
            'args' => [
                'key',
                'value',
            ]
        ]);

        (new WPOptions)->setDefault('key', 'value');
    }

    /** @test */
    public function the_set_default_method_does_not_set_a_config_value_if_one_has_already_been_set()
    {
        WP_Mock::wpFunction('add_option', [
            'times' => 0,
            'args' => [
                'key',
                'value',
            ]
        ]);

        $wpOptions = new WPOptions;

        $wpOptions->setTest('key', 'value');
        $wpOptions->setDefault('key', 'alternative_value');

        $this->assertEquals('value', $wpOptions->get('key'));
    }

    /** @test */
    public function the_is_already_set_function_returns_true_if_a_config_value_already_exists_for_the_key()
    {
        $wpOptions = new WPOptions;
        $wpOptions->setTest('key', 'value');
        $this->assertTrue($wpOptions->isAlreadySet('key'));
    }
}
