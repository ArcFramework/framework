<?php

namespace Arc\CustomPostTypes;

use Illuminate\Support\ServiceProvider;

class CustomPostTypeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register all custom post types
        $registrar = $this->app->make('wordpress.custom_post_types');
        $registrar->registerAll();
    }

    public function register()
    {
        // Bind the custom post types handler
        $this->app->instance(
            'wordpress.custom_post_types',
            $this->app->make(CustomPostTypes::class)
        );
    }
}

