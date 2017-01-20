<?php

namespace Arc\Providers;

use Arc\Config\FlatFileParser;

class ServiceProvider
{
    private $parser;

    /**
     * Require the given flat file, passing in the given variables
     **/
    public function require($file, $variables = [])
    {
        return app(FlatFileParser::class)->parse($file, $variables);
    }
}
