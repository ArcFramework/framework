<?php

namespace Arc\Config;

use Arc\BasePlugin;
use Arc\Filesystem\FileManager;

class FlatFileParser
{
    public function __construct(BasePlugin $plugin, FileManager $fileManager)
    {
        $this->plugin = $plugin;
        $this->fileManager = $fileManager;
    }

    /**
     * Requires the given config file, passing in the given variables and returns the result
     *
     * @param string $configFileName The name of the config file to be loaded
     * @param array $variables (optional) A set of key value pairs which will be passed in as
     * variables
     **/
    public function parse($configFileName, $variables = [])
    {
        foreach ($variables as $name => $value) {
            $$name = $value;
        }

        $fileName = $this->plugin->path . '/config/' . $configFileName . '.php';

        if (!file_exists($fileName)) {
            return [];
        }

        return include($fileName);
    }

    public function parseDirectory($directoryName, $variables = [])
    {
        foreach($variables as $name => $value) {
            $$name = $value;
        }

        foreach($this->fileManager->getAllFilesInDirectory($this->plugin->path . '/' . $directoryName) as $file) {
            include ($file->getPath() . '/' . $file->getFilename());
        }
    }
}
