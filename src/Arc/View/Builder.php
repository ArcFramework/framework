<?php

namespace Arc\View;

use Arc\BasePlugin;
use Arc\Exceptions\ViewNotFoundException;

class Builder
{
    protected $plugin;

    public function __construct(BasePlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Build the given view
     *
     * @return string The contents of the view
     **/
    public function build($view, $parameters = [])
    {
        return $this->plugin->make('blade')->view()->make($view, $parameters);
    }

    public function render($view)
    {
        return $this->build($view);
    }
}
