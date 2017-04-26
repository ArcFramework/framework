<?php

namespace Arc\PostTypes;

use Illuminate\Routing\ControllerDispatcher;
use Illuminate\View\Factory;

class PostTypes
{
    public $init;

    protected $controllerMethod;
    protected $controllerDispatcher;
    protected $metaBoxes;
    protected $name;
    protected $pluralName;
    protected $public;
    protected $slug;
    protected $supports;
    protected $view;

    public function __construct(Factory $viewFactory, ControllerDispatcher $controllerDispatcher)
    {
        $this->controllerDispatcher = $controllerDispatcher;
        $this->viewFactory = $viewFactory;
    }

    public function createPublic()
    {
        $this->public = true;
        return $this;
    }

    public function whichSupportsFields($fields)
    {
        $this->supports = $fields;
        return $this;
    }

    public function withMetaBoxes($metaBoxes)
    {
        $this->metaBoxes = $metaBoxes;
        return $this;
    }

    public function add()
    {
        $postType = new PostType;

        foreach(['slug', 'public', 'name', 'pluralName', 'supports','metaBoxes'] as $property) {
            $postType->$property = $this->$property;
            unset($this->property);
        }

        $this->postTypes[] = $postType;
    }

    public function register()
    {
        $this->init = function() {
            foreach ($this->postTypes as $postType) {
                register_post_type($postType->slug, [
                    'public' => $postType->public,
                    'labels' => [
                        'name' => $postType->name,
                        'plural' => $postType->pluralName,
                    ],
                    'supports' => $postType->supports ?? ['title', 'editor'],
                ]);

                if (!is_null($this->metaBoxes)) {
                    add_action('load-post.php', function() {
                        $this->setupMetaBoxes($postType);
                    });
                    add_action('load-post-new.php', function() {
                        $this->setupMetaBoxes($postType);
                    });
                }
            }
        };

        add_action('init', $this->init);

        // Register the template handler for a controller method
        if (!is_null($this->controllerMethod)) {
            app()->bind($this->slug, function() {
                return $this->controllerDispatcher->parseControllerCall($this->controllerMethod);
            });
           return $this->registerTemplateDispatcher();
        }

        // Register the template handler for a view
        if (!is_null($this->view)) {
            app()->bind($this->slug, function() {
                return $this->viewFactory->build($this->view, ['post' => get_post()]);
            });
            return $this->registerTemplateDispatcher();
        }
    }

    public function registerTemplateDispatcher()
    {
        add_filter('single_template', function() {
            global $post;
            if ($post->post_type == $this->slug) {
                return rtrim(config('plugin_path'), '/') . '/custom_post_type.php';
            }
        });
    }

    public function render($postType)
    {
        echo app($postType);
    }

    public function withPluralName($pluralName)
    {
        $this->pluralName = $pluralName;
        return $this;
    }

    public function withName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function withSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function whichCallsControllerMethod($controllerMethod)
    {
        $this->controllerMethod = $controllerMethod;
        return $this;
    }

    public function whichDisplaysView($view)
    {
        $this->view = $view;
        return $this;
    }

    public function setupMetaBoxes()
    {
        foreach ($this->metaBoxes as $metaBox) {
            add_meta_box(
                config('plugin_slug') . '-' . $metaBox['title'] . '-meta-box',
                $metaBox['title'],
                $metaBox['callback'],
                $this->slug,
                $metaBox['context'] ?? 'side',
                $metaBox['priority'] ?? 'default',
                $metaBox['callbackArguments'] ?? null
            );
        }
    }
}
