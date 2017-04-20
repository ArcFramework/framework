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
use Arc\Http\Router;
use Arc\Http\ValidatesRequests;
use Arc\Mail\Mailer;
use Arc\Providers\Providers;
use Arc\Shortcodes\Shortcodes;
use Arc\View\ViewFinder;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
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
use Illuminate\Http\Request;
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

abstract class Application extends Container implements ApplicationContract, ContainerInterface
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
     * The base path for the plugin.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * Instantiate the class
     * @param string $pluginFilename Full qualified path to plugin file
     **/
    public function __construct($pluginFilename)
    {
        $this->setPaths($pluginFilename);

        $this->bindImportantInterfaces();

        $this->registerBaseBindings();

        $this->registerBaseServiceProviders();

        $this->registerCoreContainerAliases();
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new RoutingServiceProvider($this));
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return get_plugin_data($this->filename)['Version'];
    }

    /**
     * Get the base path of the Arc installation.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Get or check the current application environment.
     *
     * @return string
     */
    public function environment()
    {
        return $this->env(func_get_args());
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return false;
    }

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        $this->make(Providers::class)->register();
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  array  $options
     * @param  bool   $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $options = [], $force = false)
    {
        if (($registered = $this->getProvider($provider)) && ! $force) {
            return $registered;
        }
        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolveProviderClass($provider);
        }
        $provider->register();
        // Once we have registered the service we will iterate through the options
        // and set each of them on the application so they will be available on
        // the actual loading of the service objects and for developer usage.
        foreach ($options as $key => $value) {
            $this[$key] = $value;
        }
        $this->markAsRegistered($provider);
        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by the developer's application logics.
        if ($this->booted) {
            $this->bootProvider($provider);
        }
        return $provider;
    }

    /**
     * Register a deferred provider and service.
     *
     * @param  string  $provider
     * @param  string  $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        // Once the provider that provides the deferred service has been registered we
        // will remove it from our local list of the deferred services with related
        // providers so that this container does not try to resolve it out again.
        if ($service) {
            unset($this->deferredServices[$service]);
        }
        $this->register($instance = new $provider($this));
        if (! $this->booted) {
            $this->booting(function () use ($instance) {
                $this->bootProvider($instance);
            });
        }
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        // Once the application has booted we will also fire some "booted" callbacks
        // for any listeners that need to do work after this initial booting gets
        // finished. This is useful when ordering the boot-up processes we run.
        $this->fireAppCallbacks($this->bootingCallbacks);
        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;
        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Register a new boot listener.
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->fireAppCallbacks([$callback]);
        }
    }

    /**
     * Get the path to the cached services.php file.
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        return $this->basePath().'/bootstrap/cache/services.json';
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        $this->instance('app', $this);

        $this->bind(ContainerContract::class, Application::class);

        $this->instance(Container::class, $this);

        $this->instance(Application::class, $this);
    }

    /**
     * Bind Important Interfaces to the container so we will be able to resolve them when needed.
     */
    protected function bindImportantInterfaces()
    {
        $this->singleton(
            Illuminate\Contracts\Http\Kernel::class,
            Arc\Http\Kernel::class
        );

        $this->singleton(
            Illuminate\Contracts\Debug\ExceptionHandler::class,
            Arc\Exceptions\Handler::class
        );
    }


    /**
     * Boots and runs the plugin
     **/
    public function start()
    {
        $this->init();
        $this->callRun();
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Run the given array of bootstrap classes.
     *
     * @param  array  $bootstrappers
     * @return void
     */
    public function bootstrapWith(array $bootstrappers)
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this['events']->fire('bootstrapping: '.$bootstrapper, [$this]);

            $this->make($bootstrapper)->bootstrap($this);

            $this['events']->fire('bootstrapped: '.$bootstrapper, [$this]);
        }
    }

    /**
     * Initialises the plugin but doesn't run it
     **/
    public function init()
    {
        $this->cronSchedules->register();
        $this->shortcodes->register();
        $this->adminMenus->register();
        $this->assets->enqueue();
    }

    /**
     * Set the shared instance of the application.
     *
     * @param  Application|null  $container
     * @return static
     */
    public abstract static function setApplicationInstance(Application $application);

    /**
     * Get the shared instance of the application.
     *
     * @return static
     */
    public abstract static function app();

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
        $this->basePath = $this->env('PLUGIN_BASE_PATH', dirname($this->filename) . '/');
        $this->slug = $this->env('PLUGIN_SLUG', pathinfo($this->filename, PATHINFO_FILENAME));
        $this->testsDirectory = $this->path . 'tests';
        $this->uri = $this->env('PLUGIN_URI', $this->getUrl());
        $this->wordpressPath = $this->env('WORDPRESS_PATH', ABSPATH);
    }

    /**
     * Bind implementations of critical interfaces to the service container
     **/
    protected function oldbindImportantInterfaces()
    {
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
