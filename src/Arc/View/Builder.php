<?php

namespace Arc\View;

use Arc\Application;
use Arc\BasePlugin;
use Arc\Exceptions\ViewNotFoundException;

class Builder
{
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Build the given view
     *
     * @return string The contents of the view
     **/
    public function build($view, $parameters = [])
    {
        return app('blade')->view()->make($view, $parameters);
    }
}
