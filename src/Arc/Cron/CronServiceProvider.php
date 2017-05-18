<?php

namespace Arc\Cron;

use Illuminate\Support\ServiceProvider;

class CronServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make(CronSchedules::class)->register();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

