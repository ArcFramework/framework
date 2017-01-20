<?php

namespace Arc\Shortcodes;

class Shortcode
{
    public $code;
    public $partial;
    public $parameters;

    public function __construct($code, $partial, $parameters = [])
    {
        $this->code = $code;
        $this->partial = $partial;
        $this->parameters = $parameters;
    }
}
