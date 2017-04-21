<?php

namespace Arc\Assets;

use Arc\Application;
use Arc\Config\FlatFileParser;
use Illuminate\Support\Str;

class Assets
{
    private $dependencies;
    private $parser;
    private $path;
    private $slug;
    private $scripts = [];
    private $styles = [];
    private $adminScripts = [];
    private $adminStyles = [];

    public function __construct(Application $plugin, FlatFileParser $parser)
    {
        $this->app = $plugin;
        $this->parser = $parser;
    }

    /**
     * Enqueues the assets into the Wordpress application
     **/
    public function enqueue()
    {
        $this->parser->parse('assets', [
            'assets' => $this
        ]);
    }

    /**
     * Enqueue the script and reset the fluent properties
     **/
    public function enqueueScript()
    {
        $this->scripts[] = $this->buildAsset('script');
    }

    /**
     * Enqueue the script and reset the fluent properties
     **/
    public function enqueueStyle()
    {
        $this->styles[] = $this->buildAsset('style');
    }

    /**
     * Enqueue the script and reset the fluent properties
     **/
    public function enqueueAdminStyle()
    {
        $this->adminStyles[] = $this->buildAsset('adminStyle');
    }

    /**
     * Enqueue the admin script and reset the fluent properties
     **/
    public function enqueueAdminScript()
    {
        $this->adminScripts[] = $this->buildAsset('adminScript');
    }

    private function buildAsset($type)
    {
        $asset = new Asset($type);

        foreach (['slug', 'path', 'dependencies'] as $key) {
            $asset->$key = $this->$key;
            $this->$key = null;
        }

        return $asset;
    }

    /**
     * Add Wordpress hooks to register the assets at the appropriate time
     **/
    public function register()
    {
        add_action('wp_enqueue_scripts', function() {
            foreach($this->scripts as $script) {
                wp_enqueue_script(
                    $script->slug,
                    $this->getPath($script),
                    $script->dependencies,
                    null
                );
            }

            foreach($this->styles as $style) {
                wp_enqueue_style(
                    $style->slug,
                    $this->getPath($style),
                    $style->dependencies,
                    null
                );
            }
        });

        add_action('admin_enqueue_scripts', function() {
            foreach($this->adminScripts as $script) {
                wp_enqueue_script(
                    $script->slug,
                    $this->getPath($script),
                    $script->dependencies,
                    null
                );
            }

            foreach($this->adminStyles as $style) {
                wp_enqueue_style(
                    $style->slug,
                    $this->getPath($style),
                    $style->dependencies,
                    null
                );
            }
        });
    }

    /**
     * Sets the path of the asset
     **/
    public function path($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Sets the dependencies of the asset
     **/
    public function dependencies($dependencies)
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    /**
     * Sets the slug of the asset
     **/
    public function slug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Expand the relative path of the asset
     *
     * @param $asset The Asset object or a string with the relative path to the assets folder
     * @return string The fully qualified path of the asset
     **/
    public function getPath($asset)
    {
        if ($asset instanceof Asset) {
            $path = $asset->path;
        }
        else {
            $path = $asset;
        };

        // If no relative path has been specified the script has no path
        if (empty($path)) {
            return null;
        }

        // If a protocol is specified, the path is external
        if (Str::contains($path, 'http')) {
            return $path;
        }

        return $this->app->uri . '/assets/' . $path;
    }
}
