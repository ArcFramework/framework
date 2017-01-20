<?php

namespace Arc;

use Illuminate\Container\Container;

class Application extends Container
{
    public $pluginDirectory;
    public $pluginFileName;
    public $pluginPath;
    public $pluginNamespace;

    public function __construct($pluginFileName = null, $pluginNamespace = null)
    {
        $this->pluginFilename = $pluginFileName;
        $this->pluginDirectory = dirname($pluginFileName);
        $this->pluginPath = plugins_url(null, $this->pluginFileName);
        $this->pluginNamespace = $pluginNamespace;
    }
}
