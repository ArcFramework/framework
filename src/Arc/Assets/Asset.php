<?php

namespace Arc\Assets;

class Asset
{
    public $path;
    public $slug;
    public $type;
    public $dependencies;

    public function __construct($type = null)
    {
        $this->type = $type;
    }
}
