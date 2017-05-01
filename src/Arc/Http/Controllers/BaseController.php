<?php

namespace Arc\Http\Controllers;

use Arc\Application;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Validation\Factory;

class BaseController
{
    public $app;
    private $validator;

    public function __construct(Application $app)
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

    public function response($content = null, $status = null, $headers = [])
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
            $redirect = $this->app->make(Redirector::class);
        }
        else {
            $redirect = $this->app->make(Redirector::class)->to($to, $status, $headers, $secure);
        }
        $redirect->setSession($this->app->make('session.store'));

        return $redirect;
    }

    public function makeValidator($request, $rules)
    {
        return $this->app->make('validator')->make($request, $rules);
    }
}
