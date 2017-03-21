<?php

namespace Arc;

use Arc\Activation\ActivationHooks;
use Arc\Admin\AdminMenus;
use Arc\Assets\Assets;
use Arc\Exceptions\Handler;
use Arc\Config\Config;
use Arc\Config\WPOptions;
use Arc\Contracts\Mail\Mailer as MailerContract;
use Arc\Cron\CronSchedules;
use Arc\Http\ValidatesRequests;
use Arc\Mail\Mailer;
use Arc\Providers\Providers;
use Arc\Routing\Router;
use Arc\Shortcodes\Shortcodes;
use Dotenv\Dotenv;
use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\MySqlBuilder;

class BasePlugin extends Container
{
    private $activationHooks;
    private $adminMenus;
    private $assets;
    private $cronSchedules;
    private $providers;
    private $router;
    private $shortcodes;
    private $validator;

    /**
     * Instantiate the class
     **/
    public function __construct($pluginFilename)
    {
        // Instantiate IoC container
        $this->app = new Container;
        $this->app->instance(Container::class, $this->app);
        Container::setInstance($this->app);

        // Get environment variables
        if (file_exists(dirname($pluginFilename) . '/.env')) {
            $dotenv = new Dotenv(dirname($pluginFilename));
            $dotenv->load();
        }

        // Bind config object
        $this->app->singleton('configuration', function() {
            return app(Config::class);
        });
        // Bind WPOptions object
        $this->app->singleton(WPOptions::class, function() {
            return new WPOptions;
        });

        // Bind Exception Handler
        $this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        // Bind HTTP Request validator
        $this->validator = $this->app->make(ValidatesRequests::class);
        $this->app->instance(
            ValidatesRequests::class,
            $this->validator
        );

        // Bind filesystem
        $this->app->bind(
            \Illuminate\Contracts\Filesystem\Filesystem::class,
            \Illuminate\Filesystem\Filesystem::class
        );

        $this->app->bind('blade', function() {
            return new \Arc\View\Blade(config('plugin_path') . '/assets/views', config('plugin_path') . '/cache');
        });

        $this->capsule = app(Capsule::class);
        $this->adminMenus = app(AdminMenus::class);
        $this->assets = app(Assets::class);
        $this->cronSchedules = app(CronSchedules::class);
        $this->providers = app(Providers::class);
        $this->router = app(Router::class);
        $this->shortcodes = app(Shortcodes::class);
        $this->app->bind('pluginFilename', function() use ($pluginFilename) {
            return $pluginFilename;
        });
        $this->pluginFilename = $pluginFilename;
    }

    /**
     * Boots the plugin
     **/
    public function boot()
    {
        // Bind version
        app()->bind('version', function() {
            return get_plugin_data($this->pluginFilename)['Version'];
        });

        global $wpdb;

        $this->capsule->addConnection([
            'driver' => 'mysql',
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASSWORD,
            'host' => '127.0.0.1',
            'prefix' => $wpdb->base_prefix,
            'collation' => !empty(DB_COLLATE) ? DB_COLLATE : 'utf8_unicode_ci'
        ]);

        $this->capsule->getContainer()->singleton(
            ExceptionHandler::class,
            Handler::class
        );
        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        // Bind schema instance
        $this->schema = $this->capsule->schema();
        app()->instance(MySqlBuilder::class, $this->schema);

        // Bind Mailer concretion
        app()->bind(MailerContract::class, Mailer::class);

        $this->providers->register();

        $this->cronSchedules->register();
        $this->shortcodes->register();
        $this->adminMenus->register();
        $this->assets->enqueue();
        $this->router->boot();
    }
}
