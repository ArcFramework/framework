<?php

namespace Arc\View;

use Arc\BasePlugin;

use Arc\Application;

class Builder
{
    private $app;

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
        foreach ($parameters as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include($this->app->pluginDirectory . '/src/views/' . $view . '.php');
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
}
