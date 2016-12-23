<?php

namespace Arc\Contracts;

interface Activator
{
    /**
     * Performs the actions neccessary when the plugin is activated
     **/
    public function activate();
}
