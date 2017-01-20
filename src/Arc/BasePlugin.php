<?php

namespace Arc;

use Arc\Activation\ActivationHooks;
use Arc\Admin\AdminMenus;
use Arc\Assets\Assets;
use Arc\Exceptions\Handler;
use Arc\Cron\CronSchedules;
use Arc\Providers\Providers;
use Arc\Routes\Router;
use Arc\Shortcodes\Shortcodes;
use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Capsule\Manager as Capsule;

class BasePlugin
{
    private $activationHooks;
    private $adminMenus;
    private $assets;
    private $cronSchedules;
    private $providers;
    private $router;
    private $shortcodes;

    /**
     * Instantiate the class
     **/
    public function __construct($pluginFilename)
    {
        // Pull in dependencies
        require (__DIR__ . "/../vendor/autoload.php");

        // Instantiate IoC container
        $this->app = new Application(
            $pluginFilename,
            substr(get_class($this), 0, strrpos(get_class($this), '\\'))
        );

        // Make sure we get this instance of the Application class every time that class
        // is resolved from the container
        app()->singleton(Application::class, function() {
            return $this->app;
        });

        // Bind Exception Handler
        app()->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        app()->bind(
            \Illuminate\Contracts\Http\Kernel::class,
            \Illuminate\Foundation\Http\Kernel::class
        );

        $this->capsule = app(Capsule::class);
        $this->adminMenus = app(AdminMenus::class);
        $this->assets = app(Assets::class);
        $this->cronSchedules = app(CronSchedules::class);
        $this->providers = app(Providers::class);
        $this->router = app(Router::class);
        $this->shortcodes = app(Shortcodes::class);
    }

    /**
     * Boots the plugin
     **/
    public function boot()
    {
        $this->capsule->addConnection([
            'driver' => 'mysql',
            'database' => 'wp',
            'username' => 'root',
            'password' => '',
            'host' => '127.0.0.1',
            'prefix' => 'mw_'
        ]);
        $this->capsule->getContainer()->singleton(
            ExceptionHandler::class,
            Handler::class
        );
        $this->capsule->bootEloquent();

        $this->cronSchedules->register();
        $this->providers->register();
        $this->shortcodes->register();
        $this->adminMenus->register();
        $this->assets->enqueue();
        $this->router->boot();
    }
}
