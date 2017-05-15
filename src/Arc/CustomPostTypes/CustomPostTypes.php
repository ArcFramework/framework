<?php

namespace Arc\CustomPostTypes;

use Arc\Application;
use WP_Post;

class CustomPostTypes
{
    protected $app;
    protected $viewFinder;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->viewFinder = $this->app->make('view.finder');
    }

    /**
     * Register all Custom Post Type class listed in the plugin's config/wordpress.php file
     * under custom_post_types
     **/
    public function registerAll()
    {
        $this->getAll()->each(function ($customPostType) {
            $this->register($customPostType);
        });
    }

    /**
     * Return the corresponding custom post type model object for the given WP_Post object
     * @param WP_Post $post
     * @return Arc\CustomPostTypes\CustomPostType
     **/
    public function resolve(WP_Post $post)
    {
        return ($this->getAll()->first(function ($customPostType) use ($post) {
            return $customPostType->getSlug() == $post->post_type;
        }))::find($post->ID);
    }

    public function getAll()
    {
        return collect($this->app->config('wordpress.custom_post_types'))->map(function ($className) {
            return $this->app->make($className);
        });
    }

    public function register(CustomPostType $customPostType)
    {
        register_post_type($customPostType->getSlug(), [
            'public' => $customPostType->isPublic(),
            'labels' => [
                'name' => $customPostType->getName(),
                'plural' => $customPostType->getPluralName(),
            ],
            'supports' => $customPostType->getSupportedFields() ?? ['title', 'editor', 'custom-fields'],
            'menu_icon' => $customPostType->getIcon(),
        ]);

        if (!is_null($customPostType->getMetaBoxes())) {
            $setupMetaBoxes = function() use ($customPostType) {
                foreach ($customPostType->getMetaBoxes() as $metaBox) {
                    add_meta_box(
                        $customPostType->getSlug() . '-' . $metaBox['title'] . '-meta-box',
                        $metaBox['title'],
                        $metaBox['callback'],
                        $customPostType->getSlug(),
                        $metaBox['context'] ?? 'side',
                        $metaBox['priority'] ?? 'default',
                        $metaBox['callbackArguments'] ?? null
                    );
                }
            };
            add_action('load-post.php', $setupMetaBoxes);
            add_action('load-post-new.php', $setupMetaBoxes);
        }

        // Register the template handler for a view
        if (!is_null($customPostType->getView())) {

            // Generate the view
            $view = $this->app->make('view')->make($customPostType->getView());

            // Get the path to the compiled cached view file
            $compiler = $this->app->make('blade.compiler');
            $compiler->compile($view->getPath());
            $compiledPath = $compiler->getCompiledPath($view->getPath());

            // Add a filter to return the compiled view as the template for this post type
            add_filter('single_template', function($original) use ($compiledPath, $customPostType) {
                global $post;
                if ($post->post_type == $customPostType->getSlug()) {
                    echo($this->app->make('view')->make($customPostType->getView(), [
                        'post' => $this->app->make(CustomPostTypes::class)->resolve($post)
                    ]));
                    die;
                }
                return $original;
            });
        }
    }
}
