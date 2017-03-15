<?php

namespace Arc\Http\Controllers;

use Arc\BasePlugin;
use Arc\Http\ValidatesRequests;

class BaseController
{
    private $plugin;
    private $validator;

    public function validate($request, $rules)
    {
        app()->make(ValidatesRequests::class)->validate($request, $rules);
    }
}
