<?php

namespace Arc\Providers;

use Arc\BasePlugin;
use Arc\Config\FlatFileParser;

class Providers
{
    private $app;
    private $parser;

    public function __construct(BasePlugin $plugin, FlatFileParser $parser)
    {
        $this->plugin = $plugin;
        $this->parser = $parser;
    }

    /**
     * Register and boot all service providers
     **/
    public function register()
    {
        foreach ($this->parser->parse('providers') as $providerClass) {
            $provider = $this->plugin->make($providerClass);
            $provider->register();
        }
        foreach ($this->parser->parse('providers') as $providerClass) {
            $provider = $this->plugin->make($providerClass);
            $provider->boot();
        }
    }
}
