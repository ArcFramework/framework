<?php

namespace Arc\Bootstrap;

use Arc\Hooks\Filters;
use Arc\Hooks\NoOpFilters;
use Arc\Hooks\WPFilters;
use Illuminate\Contracts\Foundation\Application;

class BindWordpressAdapters
{
    protected $app;

    /**
     * Bind the instances of the classes which interact with wordpress.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        if (constant('BOOT_ARC_WITHOUT_WORDPRESS')) {
            return $this->bindNoWordpressImplementations();
        }

        $this->bindWordpressImplementations();
    }

    protected function bindWordpressImplementations()
    {
        $this->app->singleton(Filters::class, WPFilters::class);
    }

    protected function bindNoWordpressImplementations()
    {
        $this->app->singleton(Filters::class, NoOpFilters::class);
    }
}
