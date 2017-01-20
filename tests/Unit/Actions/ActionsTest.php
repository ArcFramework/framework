<?php

use Arc\Actions\Actions;

class ActionsTest extends FrameworkTestCase
{
    /** @test */
    public function an_action_can_be_added_using_the_actions_class()
    {
        WP_Mock::expectActionAdded('save_post', 'special_save_post');

        $actions = $this->app->make(Actions::class);

        $actions->forHook('save_post')
            ->doThis('special_save_post');
    }
}
