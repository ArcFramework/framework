<?php

namespace Arc;

use Arc\Activation\ActivationHooks;
use Arc\Hooks\Actions;
use Arc\Admin\AdminMenus;
use Arc\Assets\Assets;
use Arc\Exceptions\Handler;
use Arc\Config\Config;
use Arc\Config\Env;
use Arc\Config\WPOptions;
use Arc\Contracts\Mail\Mailer as MailerContract;
use Arc\Cron\CronSchedules;
use Arc\Events\NonDispatcher;
use Arc\Http\Kernel;
use Arc\Http\Response;
use Arc\Http\Request;
use Arc\Http\Router;
use Arc\Http\ValidatesRequests;
use Arc\Mail\Mailer;
use Arc\Providers\Providers;
use Arc\Shortcodes\Shortcodes;
use Arc\View\ViewFinder;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Contracts\Translation\Translator as Translator;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\View\ViewFinderInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\CookieSessionHandler;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\SessionManager;
use Illuminate\Translation\Translator as IlluminateTranslator;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\LoaderInterface;
use Illuminate\Validation\Factory as IlluminateValidationFactory;
use Illuminate\Validation\Validator as IlluminateValidator;
use Interop\Container\ContainerInterface;
use SessionHandlerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class BasePlugin extends Container implements ContainerInterface
{
    public $arcDirectory;
    public $filename;
    public $namespace;
    public $path;
    public $slug;
    public $testsDirectory;
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
        $this->setPaths($pluginFilename);
        $this->bindImportantInterfaces();
    }

    /**
     * Boots and runs the plugin
     **/
    public function boot()
    {
        $this->init();
        $this->callRun();
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
    }

    /**
     * Set the shared instance of the plugin.
     *
     * @param  BasePlugin|null  $container
     * @return static
     */
    public static function setPluginContainerInstance(BasePlugin $plugin = null)
    {
        return static::$pluginInstance = $plugin;
    }

    public static function plugin()
    {
        return static::$pluginInstance;
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

        // Handle request through http kernel
        $this->actions->forHook('parse_request')->doThis(function() {
            $kernel = $this->make(KernelContract::class);

            $response = $kernel->handle(
                $request = \Illuminate\Http\Request::capture()
            );

            $response->send();
            $kernel->terminate($request, $response);
        });
    }

    public function bindInstance()
    {
        $this->instance(BasePlugin::class, static::$pluginInstance);
        $this->instance(Container::class, static::$pluginInstance);
        $this->instance(ContainerContract::class, static::$pluginInstance);
    }

    public function config($key, $default = null)
    {
        return $this->make('config')->get($key, $default);
    }

    public function env($key, $default = null)
    {
        if (isset($this->env[$key])) {
            return $this->env[$key];
        }

        $environmentFile = $this->make(Env::class);
        $environmentFile->setDirectory($this->path);

        if ($environmentFile->get($key)) {
            return $environmentFile->get($key);
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

    public function shouldSkipMiddleware()
    {
        return false;
    }

    /**
     * Generate the URL to a named route.
     *
     * @param  string  $name
     * @param  array   $parameters
     * @param  bool    $absolute
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        return $this->make(UrlGenerator::class)->route($name, $parameters, $absolute);
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function view($view = null, $data = [], $mergeData = [])
    {
        $factory = $this->make('blade')->view();
        if (func_num_args() === 0) {
            return $factory;
        }
        return $factory->make($view, $data, $mergeData);
    }

    /**
     * Set all the relative paths and other constants for the application
     * @param string $pluginFilename the fully path to the plugin file
     **/
    protected function setPaths($pluginFilename)
    {
        if (!file_exists($pluginFilename)) {
            throw new \Exception('Plugin file must exist.');
        }

        $this->arcDirectory = dirname((new \ReflectionObject($this))
            ->getMethod('__construct')
            ->getDeclaringClass()
            ->getFilename());
        $this->assetsPath = $this->path . '/assets';
        $this->filename = $pluginFilename;
        $this->namespace = substr(get_called_class(), 0, strrpos(get_called_class(), "\\"));
        $this->path = $this->env('PLUGIN_PATH', dirname($this->filename) . '/');
        $this->slug = $this->env('PLUGIN_SLUG', pathinfo($this->filename, PATHINFO_FILENAME));
        $this->testsDirectory = $this->path . 'tests';
        $this->uri = $this->env('PLUGIN_URI', $this->getUrl());
        $this->wordpressPath = $this->env('WORDPRESS_PATH', ABSPATH);
    }

    /**
     * Bind implementations of critical interfaces to the service container
     **/
    protected function bindImportantInterfaces()
    {
        $this->setPluginContainerInstance($this);
        $this->bindInstance();

        $this->singleton(
            KernelContract::class,
            Kernel::class
        );
        $this->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        // Bind config object
        $this->singleton('config', Config::class);

        // Bind WPOptions object
        $wpOptions = $this->make(WPOptions::class);
        $this->instance(WPOptions::class, $wpOptions);

        // Bind Actions object
        $this->singleton('actions', function() {
            return new Actions;
        });

        // Bind session handler
        $this->singleton('session', function ($app) {
            return new SessionManager($app);
        });

        // Set default session driver
        $this['config']['session.driver'] = 'file';

        $this->singleton('session.store', function ($app) {
            // First, we will create the session manager which is responsible for the
            // creation of the various session drivers when they are needed by the
            // application instance, and will resolve them on a lazy load basis.
            return $app->make('session')->driver();
        });
        $this->singleton(StartSession::class);

        // Bind event dispatcher
        $this->bind(DispatcherContract::class, NonDispatcher::class);

        // Bind HTTP Response
        $this->bind(IlluminateResponse::class, Response::class);
        $response = $this->make(Response::class);
        $this->instance('response', $response);

        // Bind HTTP Request
        $this->bind(IlluminateRequest::class, Request::class);

        // HTTP Validation
        $this->bind(ValidationFactory::class, IlluminateValidationFactory::class);
        $this->bind(Validator::class, IlluminateValidator::class);

        // Translation
        $this->bind(Translator::class, IlluminateTranslator::class);
        $this->when(IlluminateTranslator::class)
            ->needs('$locale')
            ->give('en');
        $this->bind(LoaderInterface::class, FileLoader::class);
        $this->when(FileLoader::class)
            ->needs('$path')
            ->give(realpath($this->arcDirectory . '/../../lang'));

        // Bind route URL generator
        $this->bind('url', UrlGenerator::class);

        // Bind filesystem
        $this->bind(
            \Illuminate\Contracts\Filesystem\Filesystem::class,
            \Illuminate\Filesystem\Filesystem::class
        );

        $this->bind('blade', function() {
            return new \Arc\View\Blade($this->path . '/assets/views', $this->path . '/cache', null, $this);
        });
        $this->instance(ViewFactory::class, $this->make('blade')->view());

        // Bind Mailer concretion
        $this->bind(MailerContract::class, Mailer::class);

        // Bind version
        $this->bind('version', function() {
            return get_plugin_data($this->filename)['Version'];
        });
        $router = $this->make(Router::class);
        $this->instance(Router::class, $router);
        $this->instance(RouteCollection::class, $router->getRoutes());

        $this->capsule = $this->make(Capsule::class);
        $this->adminMenus = $this->make(AdminMenus::class);
        $this->assets = $this->make(Assets::class);
        $this->cronSchedules = $this->make(CronSchedules::class);
        $this->providers = $this->make(Providers::class);
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

        $this->instance('db', $this->capsule->getDatabaseManager());

        // Bind schema instance
        $this->schema = $this->capsule->schema();
        $this->instance(MySqlBuilder::class, $this->schema);
    }

    public function terminate()
    {

    }

    /**
     * Throw an HttpException with the given data.
     *
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function abort($code, $message = '', array $headers = [])
    {
        if ($code == 404) {
            throw new NotFoundHttpException($message);
        }
        throw new HttpException($code, $message, null, $headers);
    }

    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->make('session');
        }
        if (is_array($key)) {
            return $this->make('session')->put($key);
        }
        return make('session')->get($key, $default);
    }

    public function resourcePath($path)
    {
        return $this->path.DIRECTORY_SEPARATOR.'resources'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
