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

abstract class BasePlugin extends Container
{
    public $filename;
    public $namespace;
    public $path;
    public $slug;
    public $uri;

    /**
     * The current globally available plugin instance (if any).
     *
     * @var static
     */
    protected static $pluginInstance;

    protected $activationHooks;
    protected $adminMenus;
    protected $assets;
    protected $cronSchedules;
    protected $env;
    protected $providers;
    protected $router;
    protected $shortcodes;
    protected $validator;

    /**
     * Instantiate the class
     **/
    public function __construct($pluginFilename)
    {
        $this->filename = $pluginFilename;
        $this->namespace = substr(get_called_class(), 0, strrpos(get_called_class(), "\\"));
        $this->path = $this->env('PLUGIN_PATH', dirname($this->filename) . '/');
        $this->slug = $this->env('PLUGIN_SLUG', pathinfo($this->filename, PATHINFO_FILENAME));
        $this->uri = $this->env('PLUGIN_URI', $this->getUrl());

        $this->setPluginContainerInstance($this);
        $this->bindInstance();
    }

    /**
     * Boots the plugin
     **/
    public function boot()
    {
        // Bind config object
        $this->singleton('configuration', function() {
            return $this->make(Config::class);
        });

        // Bind WPOptions object
        $this->singleton(WPOptions::class, function() {
            return $this->make(WPOptions::class);
        });

        // Bind Exception Handler
        $this->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        // Bind HTTP Request validator
        $this->validator = $this->make(ValidatesRequests::class);
        $this->instance(
            ValidatesRequests::class,
            $this->validator
        );

        // Bind filesystem
        $this->bind(
            \Illuminate\Contracts\Filesystem\Filesystem::class,
            \Illuminate\Filesystem\Filesystem::class
        );

        $this->bind('blade', function() {
            return new \Arc\View\Blade(config('plugin_path') . '/assets/views', config('plugin_path') . '/cache');
        });

        $this->capsule = $this->make(Capsule::class);
        $this->adminMenus = $this->make(AdminMenus::class);
        $this->assets = $this->make(Assets::class);
        $this->cronSchedules = $this->make(CronSchedules::class);
        $this->providers = $this->make(Providers::class);
        $this->router = $this->make(Router::class);
        $this->shortcodes = $this->make(Shortcodes::class);
        $this->bind('pluginFilename', function() use ($pluginFilename) {
            return $pluginFilename;
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
        $this->instance(MySqlBuilder::class, $this->schema);

        // Bind Mailer concretion
        $this->bind(MailerContract::class, Mailer::class);

        $this->providers->register();
    /**
     * Set the shared instance of the plugin.
     *
     * @param  BasePlugin|null  $container
     * @return static
     */
    public static function setPluginContainerInstance(BasePlugin $plugin = null)
    {
        if (!is_null(static::$pluginInstance)) {
            return;
        }
        return static::$pluginInstance = $plugin;
    }

    public static function plugin()
    {
        return static::$pluginInstance;
    }

        $this->cronSchedules->register();
        $this->shortcodes->register();
        $this->adminMenus->register();
        $this->assets->enqueue();
        $this->router->boot();
    }

    public function bindInstance()
    {
        $this->instance(BasePlugin::class, $this);
    }

    public function config($key, $default = null)
    {
        return $this->make('configuration')->get($key, $default);
    }

    public function env($key, $default = null)
    {
        if (!isset($this->env[$key])) {
            return $default;
        }

        return $this->env[$key];
    }
}
