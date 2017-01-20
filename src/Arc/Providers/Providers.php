<?php

namespace Arc\Providers;

use Arc\Application;
use Arc\Config\FlatFileParser;

class Providers
{
    private $app;
    private $parser;

    public function __construct(Application $app, FlatFileParser $parser)
    {
        $this->app = $app;
        $this->parser = $parser;
    }

    /**
     * Register and boot all service providers
     **/
    public function register()
    {
        foreach ($this->parser->parse('providers') as $providerClass) {
            $provider = $this->app->make($providerClass);
            $provider->boot();
        }
    }
}
