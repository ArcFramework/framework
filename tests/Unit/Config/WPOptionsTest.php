<?php

use Arc\Config\WPOptions;
use Arc\Hooks\Filters;

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

        $this->app->make(WPOptions::class)->get('key');
    }

    /** @test */
    public function the_set_method_calls_the_add_option_function_when_there_is_no_existing_option()
    {
        WP_Mock::wpFunction('get_option', [
            'times' => 1,
            'args' => [
                'key',
            ],
            'return' => false
        ]);

        WP_Mock::wpFunction('add_option', [
            'times' => 1,
            'args' => [
                'key',
                'value',
            ]
        ]);

        $this->app->make(WPOptions::class)->set('key', 'value');
    }

    /** @test */
    public function the_set_method_calls_the_update_option_function_when_there_is_an_existing_option()
    {
        WP_Mock::wpFunction('get_option', [
            'times' => 1,
            'args' => [
                'key',
            ],
            'return' => true
        ]);

        WP_Mock::wpFunction('update_option', [
            'times' => 1,
            'args' => [
                'key',
                'value',
            ]
        ]);

        $this->app->make(WPOptions::class)->set('key', 'value');
    }

    /** @test */
    public function the_set_test_method_sets_a_test_value_without_touching_wordpress_api()
    {
        WP_Mock::wpFunction('add_option', [
            'times' => 0,
        ]);

        $wpOptions = $this->app->make(WPOptions::class);
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

        $this->app->make(WPOptions::class)->set('key', 'value');
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

        $wpOptions = $this->app->make(WPOptions::class);

        $wpOptions->setTest('key', 'value');
        $wpOptions->setDefault('key', 'alternative_value');

        $this->assertEquals('value', $wpOptions->get('key'));
    }

    /** @test */
    public function the_is_already_set_function_returns_true_if_a_config_value_already_exists_for_the_key()
    {
        $wpOptions = $this->app->make(WPOptions::class);
        $wpOptions->setTest('key', 'value');
        $this->assertTrue($wpOptions->isAlreadySet('key'));
    }

    /** @test */
    public function the_set_default_from_address_method_sets_the_default_wordpress_from_email_address()
    {
        $filters = Mockery::mock(Filters::class);
        $filters->shouldReceive('forHook')->with('wp_mail_from')->once()->andReturn($filters);
        $filters->shouldReceive('doThis')
            ->with(\Mockery::on(function($arg) {
                return call_user_func($arg) == 'from@domain.com';
            }))
            ->once()
            ->andReturn($filters);
        $filters->shouldReceive('forHook')->with('wp_mail_from_name')->once()->andReturn($filters);
        $filters->shouldReceive('doThis')->once();
        $this->app->instance(Filters::class, $filters);

        $this->app->forgetInstance(WPOptions::class);

        $wpOptions = $this->app->make(WPOptions::class);
        $wpOptions->setDefaultFromAddress('from@domain.com');
    }

    /** @test */
    public function the_default_from_address_is_set_method_returns_true_if_the_default_wordpress_address_is_set()
    {
        WP_Mock::onFilter('wp_mail_from')
            ->with('')
            ->reply('some@address.com');

        $this->assertTrue($this->app->make(WPOptions::class)->defaultFromAddressIsSet());
    }
}
