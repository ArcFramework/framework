<?php

namespace Arc\Admin;

use Arc\Application;
use Arc\View\Builder;

class AdminMenus
{
    private $name;
    private $title;
    private $capability;
    private $slug;
    private $view;
    private $viewBuilder;
    private $icon;
    private $position;
    private $settings;

    public function __construct(Application $app, Builder $viewBuilder)
    {
        $this->app = $app;
        $this->viewBuilder = $viewBuilder;
    }

    public function register()
    {
        $adminRegistrarClassName = $this->app->pluginNamespace . '\\Admin\\RegistersAdminMenus';

        // If no activator class has been defined we can return early
        if (!class_exists($adminRegistrarClassName)) {
            return;
        }

        $registrar = new $adminRegistrarClassName($this);
        $registrar->register();
    }

    protected function add()
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
                [$this, 'render' . $this->view],
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
        echo($this->viewBuilder->build($view));
    }

    protected function addMenuPageCalled($name)
    {
        $this->name = $name;
        return $this;
    }

    protected function withMenuTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    protected function restrictedToCapability($capability)
    {
        $this->capability = $capability;
        return $this;
    }

    protected function withSettings($settings = [])
    {
        $this->settings = $settings;
        return $this;
    }

    protected function withSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    protected function whichRendersView($view)
    {
        $this->view = $view;
        return $this;
    }

    protected function withIcon($icon)
    {
        $this->icon = $this->plugin->pluginPath . '/src/assets/images/' . $icon;
        return $this;
    }
}
