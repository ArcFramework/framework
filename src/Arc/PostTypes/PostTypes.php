<?php

namespace Arc\PostTypes;

class PostTypes
{
    private $name;
    private $pluralName;
    private $public;
    private $slug;

    public function createPublic()
    {
        $this->public = true;
        return $this;
    }

    public function register()
    {
        add_action('init', function() {
            register_post_type($this->slug, [
                'public' => $this->public,
                'labels' => [
                    'name' => $this->name,
                    'plural' => $this->pluralName,
                ]
            ]);
        });
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
}
