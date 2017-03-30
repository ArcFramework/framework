<?php

namespace Arc;

use Arc\Activation\ActivationHooks;
use Arc\Hooks\Actions;
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
use Interop\Container\ContainerInterface;

abstract class BasePlugin extends Container implements ContainerInterface
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

        // Bind config object
        $this->singleton('configuration', function() {
            return $this->make(Config::class);
        });

        // Bind WPOptions object
        $wpOptions = $this->make(WPOptions::class);
        $this->instance(WPOptions::class, $wpOptions);

        // Bind Actions object
        $this->singleton('actions', function() {
            return new Actions;
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
            return new \Arc\View\Blade($this->path . '/assets/views', $this->path . '/cache', null, $this);
        });

        $this->capsule = $this->make(Capsule::class);
        $this->adminMenus = $this->make(AdminMenus::class);
        $this->assets = $this->make(Assets::class);
        $this->cronSchedules = $this->make(CronSchedules::class);
        $this->providers = $this->make(Providers::class);
        $this->router = $this->make(Router::class);
        $this->shortcodes = $this->make(Shortcodes::class);

        global $wpdb;

        $this->capsule->addConnection([
            'driver' => 'mysql',
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASSWORD,
            'host' => '127.0.0.1',
            'prefix' => $wpdb->base_prefix ?? null,
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

        // Bind version
        $this->bind('version', function() {
            return get_plugin_data($this->filename)['Version'];
        });
    }

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

    /**
     * Boots and runs the plugin
     **/
    public function boot()
    {
        $this->init();

        // Exit early if we are testing
        if (defined('ARC_TESTING')) {
            return;
        }

        $this->callRun();
    }

    /**
     * Call the 'run' method on the plugin class if it exists, injecting any dependencies
     **/
    public function callRun()
    {
        // Run plugin
        if (method_exists($this, 'run')) {
            $this->call([$this, 'run']);
        }
    }

    /**
     * Initialises the plugin but doesn't run it
     **/
    public function init()
    {

        $this->make(Providers::class)->register();

        $this->cronSchedules->register();
        $this->shortcodes->register();
        $this->adminMenus->register();
        $this->assets->enqueue();
        $this->router->boot();
    }

    public function bindInstance()
    {
        $this->instance(BasePlugin::class, static::$pluginInstance);
    }

    public function config($key, $default = null)
    {
        return $this->make('configuration')->get($key, $default);
    }

    public function env($key, $default = null)
    {
        if (isset($this->env[$key])) {
            return $this->env[$key];
        }

        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        return $default;
    }

    protected function getUrl()
    {
        if (!function_exists('get_site_url')) {
            return null;
        }
        return get_site_url() . '/wp-content/plugins/' . $this->slug;
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        return $this->make($id);
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return $this->bound($id);
    }
}
