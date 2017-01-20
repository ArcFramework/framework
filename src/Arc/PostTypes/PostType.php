<?php

namespace Arc\PostTypes;

class PostType
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
     * Creates a post of the given post type with the given attributes and returns the post id
     *
     * @param string $postType The slug of the post type
     * @param array $attributes
     * @return int The post id of the newly minted post
     */
    public static function create($postType, $attributes)
    {
        $nativeAttributes = self::filterNativeAttributes($attributes);
        $customAttributes = self::filterCustomAttributes($attributes);

        // Insert the post
        $postId = wp_insert_post($nativeAttributes->merge(
            ['post_type' => $postType]
        )->toArray(), true);

        // Append the custom fields to the post
        $customAttributes->each(function ($value, $key) use ($postId) {
            add_post_meta($postId, $key, $value);
        });

        return $postId;
    }

    public static function filterCustomAttributes($attributes)
    {
        return collect($attributes)->filter(function ($attribute, $key) {
            return !collect(self::NATIVE_ATTRIBUTES)->contains($key);
        });
    }

    public static function filterNativeAttributes($attributes)
    {
        return collect($attributes)->filter(function ($attribute, $key) {
            return collect(self::NATIVE_ATTRIBUTES)->contains($key);
        });
    }
}
