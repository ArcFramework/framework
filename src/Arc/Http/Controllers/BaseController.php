<?php

namespace Arc\Http\Controllers;

use Arc\BasePlugin;
use Arc\Http\ValidatesRequests;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\ResponseFactory;

class BaseController
{
    public $app;
    private $validator;

    public function __construct(BasePlugin $app)
    {
        $this->app = $app;
    }

    public function validate($request, $rules)
    {
        $this->app->make(ValidatesRequests::class)->validate($request, $rules);
    }

    public function response($content = null, $status = null, $headers = null)
    {
        $factory = $this->app->make(ResponseFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($content, $status, $headers);
    }

    /**
     * Get an instance of the redirector.
     *
     * @param  string|null  $to
     * @param  int     $status
     * @param  array   $headers
     * @param  bool    $secure
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
        if (is_null($to)) {
            return $this->app->make(Redirector::class);
        }
        return $this->app->make(Redirector::class)->to($to, $status, $headers, $secure);
    }
}
