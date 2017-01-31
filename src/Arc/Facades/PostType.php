<?php

namespace Arc\Facades;

use Arc\PostTypes\PostTypes;

class PostType
{
    public static function render($postType)
    {
        return app(PostTypes::class)->render($postType);
    }
}
