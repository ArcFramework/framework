<?php

namespace Arc\Http;

use Arc\BasePlugin;

class BaseController
{
    private $plugin;
    private $validator;

    public function __construct(BasePlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function validate($request, $rules)
    {
        $this->plugin->validator->validate($request, $rules);
    }
}
