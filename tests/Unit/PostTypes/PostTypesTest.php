<?php

use Arc\PostTypes\PostTypes;

class PostTypesTest extends FrameworkTestCase
{
    /** @test */
    public function a_post_type_can_be_added_using_the_post_types_class()
    {
        WP_Mock::wpFunction('register_post_type', [
            'times' => 1,
            'args' => [
                'residential_property',
                [
                    'labels' => [
                        'name' => 'Residential Property',
                        'plural' => 'Residential Properties'
                    ],
                    'public' => true
                ]
            ]
        ]);

        $postTypes = $this->app->make(PostTypes::class);

        $postTypes->createPublic()
            ->withSlug('residential_property')
            ->withPluralName('Residential Properties')
            ->withName('Residential Property')
            ->register();
    }
}

