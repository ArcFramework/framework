<?php

namespace Arc\Contracts;

interface Deactivator
{
    /**
     * Performs the actions neccessary when the plugin is deactivated
     **/
    public function deactivate();
}

