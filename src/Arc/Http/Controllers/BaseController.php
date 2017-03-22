<?php

namespace Arc\Http\Controllers;

use Arc\BasePlugin;
use Arc\Http\ValidatesRequests;

class BaseController
{
    public $plugin;
    private $validator;

    public function validate($request, $rules)
    {
        $this->plugin->make(ValidatesRequests::class)->validate($request, $rules);
    }

    public function setPluginInstance(BasePlugin $plugin)
    {
        $this->plugin = $plugin;
    }
}
