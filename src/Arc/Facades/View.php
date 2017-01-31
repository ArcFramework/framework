<?php

namespace Arc\Facades;

use Arc\View\Builder;

class View
{
    public static function render($view, $variables = [])
    {
        return app(Builder::class)->build($view, $variables);
    }
}
