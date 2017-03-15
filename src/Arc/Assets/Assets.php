<?php

namespace Arc\Assets;

use Arc\Config\FlatFileParser;

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

    public function __construct(FlatFileParser $parser)
    {
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
     * @param Script The Asset object
     * @return string The fully qualified path of the asset
     **/
    private function getPath(Asset $asset)
    {
        // If no relative path has been specified the script has no path
        if (empty($asset->path)) {
            return null;
        }

        return config('plugin_uri') . '/assets/' . $asset->path;
    }
}
