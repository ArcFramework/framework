<?php

namespace Arc\Console;

use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    protected $commands = [
        'command.ship'            => ShipPluginCommand::class,
        'command.make.controller' => GenerateControllerCommand::class,
        //'command.make.migration'    => GenerateMigrationCommand::class,
        'command.make.provider' => GenerateProviderCommand::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->commands as $key => $className) {
            $this->app->singleton($key, function ($app) use ($className) {
                return $this->app->make($className);
            });
        }

        $this->commands(array_keys($this->commands));
    }
}
