<?php

namespace Arc\Shortcodes;

use Illuminate\View\Factory;

class Shortcodes
{
    public $code;
    protected $plugin;
    protected $shortcodes = [];
    protected $viewFactory;

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function code($code)
    {
        $this->code = $code;

        return $this;
    }

    public function rendersView($view, $parameters = [])
    {
        $this->shortcodes[$this->code] = new Shortcode($this->code, $view, $parameters);
        $this->code = null;

        return $this;
    }

    /**
     * Registers the object's array of shortcodes in wordpress.
     **/
    public function register()
    {
        foreach ($this->shortcodes as $shortcode) {
            $this->registerInWordpress($shortcode);
        }
    }

    /**
     * Register the shortcode in Wordpress.
     **/
    public function registerInWordpress(Shortcode $shortcode)
    {
        add_shortcode($shortcode->code, [$this, 'render']);
    }

    /**
     * Renders a shortcode when it is used in a wordpress page or post.
     *
     * @param array|null  $attributes    The shortcode attributes if any
     * @param string|null $content       The content between the shortcodes if any
     * @param string      $shortCodeName The name of the shortcode
     **/
    public function render($attributes, $content, $shortcodeName)
    {
        $shortcode = $this->shortcodes[$shortcodeName];

        return $this->viewFactory->make($shortcode->partial, array_merge([
            'attributes'    => $attributes,
            'content'       => $content,
            'shortcodeName' => $shortcodeName,
        ], $shortcode->parameters));
    }
}
