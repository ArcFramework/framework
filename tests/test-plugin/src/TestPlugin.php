<?php

namespace Arc\TestPlugin;

use Arc\Plugin;

class TestPlugin extends Plugin
{
    public function __construct(
        HandlesActivation $activator,
        HandlesDeactivation $deactivator
    )
    {
        $this->setActivator($activator);
        $this->setDeactivator($deactivator);
    }
}
