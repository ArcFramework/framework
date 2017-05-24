<?php

namespace Arc\Admin;

use Arc\Application;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\View\Factory;

class AdminMenus
{
    private $name;
    private $title = '';
    private $capability = 'administrator';
    private $controller;
    private $controllerMethod;
    private $slug;
    private $view;
    private $viewFactory;
    private $viewParameters = [];
    private $icon;
    private $position;
    private $settings = [];

    public function __construct(
        Application $plugin,
        Factory $viewFactory,
        ControllerDispatcher $controllerDispatcher
    ) {
        $this->app = $plugin;
        $this->controllerDispatcher = $controllerDispatcher;
        $this->viewFactory = $viewFactory;
    }

    public function register()
    {
        $adminRegistrarClassName = $this->app->namespace.'\\Admin\\RegistersAdminMenus';

        // If no activator class has been defined we can return early
        if (!class_exists($adminRegistrarClassName)) {
            return;
        }

        $this->app->make($adminRegistrarClassName)->register();
    }

    public function add()
    {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', function () {
            add_menu_page(
                $this->name,
                $this->title,
                $this->capability,
                $this->slug,
                $this->getCallable(),
                $this->icon,
                $this->position
            );
        });

        foreach ($this->settings as $setting) {
            add_action('admin_init', function () use ($setting) {
                register_setting($this->slug, $setting);
            });
        }
    }

    public function __call($functionName, $args)
    {
        if (substr($functionName, 0, 6) == 'render') {
            return $this->render(substr($functionName, 6));
        }
    }

    public function render($view)
    {
        echo $this->viewFactory->make($view, $this->viewParameters);
    }

    public function addMenuPageCalled($name)
    {
        $this->name = $name;

        return $this;
    }

    public function withMenuTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function restrictedToCapability($capability)
    {
        $this->capability = $capability;

        return $this;
    }

    public function withSettings($settings = [])
    {
        $this->settings = $settings;

        return $this;
    }

    public function withSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function whichCallsControllerMethod($controllerMethod)
    {
        $parameters = explode('@', $controllerMethod);
        $this->controller = $parameters[0];
        $this->controllerMethod = $parameters[1];

        return $this;
    }

    public function whichRendersView($view, $parameters = [])
    {
        $this->view = $view;
        $this->viewParameters = $parameters;

        return $this;
    }

    public function withIcon($icon)
    {
        $this->icon = $this->app->getUrl().'/resources/assets/images/'.$icon;

        return $this;
    }

    protected function getCallable()
    {
        if (!empty($this->controller)) {
            return function () {
                $this->controllerDispatcher->call($this->controller, $this->controllerMethod);
            };
        }

        return !is_null($this->view) ? [$this, 'render'.$this->view] : function () {
        };
    }
}
