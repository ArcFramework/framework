<?php

namespace Arc\Http\Controllers;

use Arc\BasePlugin;

class BaseController
{
    private $plugin;
    private $validator;

    public function validate($request, $rules)
    {
        $this->plugin->validator->validate($request, $rules);
    }
}
