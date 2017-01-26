<?php

namespace Arc\Admin;

use Arc\Application;
use Arc\View\Builder;

class AdminMenus
{
    private $name;
    private $title = '';
    private $capability = 'administrator';
    private $slug;
    private $view;
    private $viewBuilder;
    private $viewParameters = [];
    private $icon;
    private $position;
    private $settings = [];

    public function __construct(Application $app, Builder $viewBuilder)
    {
        $this->app = $app;
        $this->viewBuilder = $viewBuilder;
    }

    public function register()
    {
        $adminRegistrarClassName = config('plugin_namespace') . '\\Admin\\RegistersAdminMenus';

        // If no activator class has been defined we can return early
        if (!class_exists($adminRegistrarClassName)) {
            return;
        }

        $registrar = new $adminRegistrarClassName($this);
        $registrar->register();
    }

    public function add()
    {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', function() {
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
            add_action('admin_init', function() use ($setting) {
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
        echo($this->viewBuilder->build($view, $this->viewParameters));
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

    public function whichRendersView($view, $parameters = [])
    {
        $this->view = $view;
        $this->viewParameters = $parameters;
        return $this;
    }

    public function withIcon($icon)
    {
        $this->icon = config('plugin_path') . '/src/assets/images/' . $icon;
        return $this;
    }

    protected function getCallable()
    {
        return !is_null($this->view) ? [$this, 'render' . $this->view] : function() {};
    }
}
