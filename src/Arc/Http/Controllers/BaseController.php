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

    /**
     * Throw an HttpException with the given data.
     *
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function abort($code, $message = '', array $headers = [])
    {
        $this->app->abort($code, $message, $headers);
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
