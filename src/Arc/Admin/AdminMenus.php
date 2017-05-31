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
    private $parent;
    private $type;

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

        $pageName = $this->name ?? $this->app->pluginName();
        $menuTitle = $this->title ?? $this->app->pluginName();
        $capability = $this->capability ?? 'administrator';
        $slug = $this->slug ?? $this->app->slug;

        add_action('admin_menu', function () {
            if ($this->type == 'menu') {
                add_menu_page($pageName, $menuTitle, $capability, $slug, $this->icon, $this->position);
            } elseif ($this->type == 'submenu') {
                add_submenu_page($this->parent, $pageName, $menuTitle, $capability, $slug, $this->getCallable());
            } elseif ($this->type = 'options') {
                add_options_page($pageName, $menuTitle, $capability, $slug, $this->getCallable());
            }
        });

        foreach ($this->settings as $setting) {
            add_action('admin_init', function () use ($slug, $setting) {
                register_setting($slug, $setting);
            });
        }
    }

    public function __call($functionName, $args)
    {
        if (substr($functionName, 0, 6) == 'render') {
            return $this->render(substr($functionName, 6));
        }
    }

    public function called($name)
    {
        $this->name = $name;

        return $this;
    }

    public function render($view)
    {
        echo $this->viewFactory->make($view, $this->viewParameters);
    }

    public function addMenuPageCalled($name)
    {
        $this->type = 'menu';

        $this->name = $name;

        return $this;
    }

    public function addSubMenuPageUnder($parent)
    {
        $this->type = 'submenu';

        $this->parent = $parent;

        return $this;
    }

    public function addSettingsPage()
    {
        $this->type = 'options';

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

    public function withIconImage($url)
    {
        $this->icon = $this->app->getUrl().'/resources/assets/images/'.$icon;

        return $this;
    }

    public function withIcon($icon)
    {
        $this->icon = $icon;

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
