<?php

namespace Arc;

use Arc\Activation\ActivationHooks;
use Arc\Admin\AdminMenus;
use Arc\Assets\Assets;
use Arc\Exceptions\Handler;
use Arc\Config\Config;
use Arc\Cron\CronSchedules;
use Arc\Providers\Providers;
use Arc\Routing\Router;
use Arc\Shortcodes\Shortcodes;
use Dotenv\Dotenv;
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
        // Instantiate IoC container
        $app = new Container;
        Container::setInstance($app);

        // Get environment variables
        $dotenv = new Dotenv(dirname($pluginFilename));
        $dotenv->load();

        // Bind config object
        $app->singleton('config', function() {
            return app(Config::class);
        });

        // Bind Exception Handler
        $app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        // Bind filesystem
        $app->bind(
            \Illuminate\Contracts\Filesystem\Filesystem::class,
            \Illuminate\Filesystem\Filesystem::class
        );

        $app->bind('blade', function() {
            return new \Arc\View\Blade(config('plugin_path') . 'assets/views', config('plugin_path') . 'cache');
        });

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
        global $wpdb;

        $this->capsule->addConnection([
            'driver' => 'mysql',
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASSWORD,
            'host' => '127.0.0.1',
            'prefix' => $wpdb->base_prefix,
            'collation' => DB_COLLATE
        ]);

        $this->capsule->getContainer()->singleton(
            ExceptionHandler::class,
            Handler::class
        );
        $this->capsule->bootEloquent();

        $this->providers->register();

        $this->cronSchedules->register();
        $this->shortcodes->register();
        $this->adminMenus->register();
        $this->assets->enqueue();
        $this->router->boot();
    }
}
