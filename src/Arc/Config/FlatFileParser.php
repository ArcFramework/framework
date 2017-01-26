<?php

namespace Arc\Config;

use Arc\Application;

class FlatFileParser
{
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

        $fileName = config('plugin_path') . '/config/' . $configFileName . '.php';

        if (!file_exists($fileName)) {
            return [];
        }

        return include($fileName);
    }
}
