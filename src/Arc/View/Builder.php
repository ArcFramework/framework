<?php

namespace Arc\View;

use Arc\Application;
use Arc\Exceptions\ViewNotFoundException;

class Builder
{
    protected $plugin;

    public function __construct(Application $plugin)
    {
        $this->app = $plugin;
    }

    /**
     * Build the given view
     *
     * @return string The contents of the view
     **/
    public function build($view, $parameters = [])
    {
        return $this->app->make('blade')->view()->make($view, $parameters);
    }

    public function render($view, $parameters = [])
    {
        return $this->build($view, $parameters);
    }
}
