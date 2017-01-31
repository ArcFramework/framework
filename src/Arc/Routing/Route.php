<?php

namespace Arc\Routing;

class Route
{
    public $verb;
    public $uri;
    public $callback;

    public function __construct($verb, $uri, $callback)
    {
        $this->verb = $verb;
        $this->uri = $uri;
        $this->callback = $callback;
    }
}
