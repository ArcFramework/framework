<?php

use Arc\PostTypes\PostTypes;

function register_post_type(...$args)
{
    return PostTypesTest::$functions->register_post_type(...$args);
}

class PostTypesTest extends FrameworkTestCase
{
    /** @test */
    public function a_post_type_can_be_added_using_the_post_types_class()
    {
        self::$functions->shouldReceive('register_post_type')->once();

        $postTypes = $this->app->make(PostTypes::class);

        $postTypes->createPublic()
            ->withSlug('residential_property')
            ->withPluralName('Residential Properties')
            ->withName('Residential Property')
            ->add();

        $postTypes->register();

        call_user_func($postTypes->init);
    }
}

