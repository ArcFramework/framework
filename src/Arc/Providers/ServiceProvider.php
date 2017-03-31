<?php

namespace Arc\Providers;

use Arc\BasePlugin;
use Arc\Config\FlatFileParser;

class ServiceProvider
{
    protected $app;
    protected $parser;

    public function __construct(BasePlugin $app)
    {
        $this->app = $app;
        $this->parser = $this->app->make(FlatFileParser::class);
    }

    /**
     * Require the given flat file, passing in the given variables
     **/
    public function require($file, $variables = [])
    {
        return $this->parser->parse($file, $variables);
    }

    public function boot()
    {

    }

    public function register()
    {

    }
}
