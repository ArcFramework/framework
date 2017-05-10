<?php

namespace Arc\CustomPostTypes;

use Arc\Models\Post;

class CustomPostType extends Post
{
    const NATIVE_ATTRIBUTES = [
        'ID',
        'post_author',
        'post_date',
        'post_date_gmt',
        'post_content',
        'post_content_filtered',
        'post_title',
        'post_excerpt',
        'post_status',
        'post_type',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_parent',
        'menu_order',
        'post_mime_type',
        'guid',
        'post_category',
        'tax_input',
        'meta_input'
    ];

    /**
     * Creates a post of the given post type with the given attributes and returns the model
     *
     * @param array $attributes
     * @return int The post id of the newly minted post
     */
    public static function create($attributes = [])
    {
        // Get the name of the class for which the method was called
        $className = get_called_class();

        // Insert the post
        $post = (new $className);
        foreach($attributes as $key => $value) {
            $post->$key = $value;
        }
        $post->save();

        // Append the custom fields to the post
        collect($customAttributes)->each(function ($value, $key) use ($post) {
            add_post_meta($post->ID, $key, $value);
        });

        return $post;
    }

    public static function filterCustomAttributes($attributes)
    {
        return collect($attributes)->filter(function ($attribute, $key) {
            return !collect(self::NATIVE_ATTRIBUTES)->contains($key);
        })->toArray();
    }

    public static function filterNativeAttributes($attributes)
    {
        return collect($attributes)->filter(function ($attribute, $key) {
            return collect(self::NATIVE_ATTRIBUTES)->contains($key);
        })->toArray();
    }

    /**
     * Returns the slug of the custom post type class
     * @return string
     **/
    public function getSlug()
    {
        return $this->slug;
    }

    public function getView()
    {
        return $this->view;
    }

    public function isPublic()
    {
        return (bool) $this->public;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPluralName()
    {
        return $this->pluralName;
    }

    public function getSupportedFields()
    {
        return $this->supportsFields;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function getMetaBoxes()
    {
        return $this->metaBoxes;
    }
}
