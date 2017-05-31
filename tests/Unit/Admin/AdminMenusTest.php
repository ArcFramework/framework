<?php

use Arc\Admin\AdminMenus;

class AdminMenusTestTest extends FrameworkTestCase
{
    /** @test */
    public function the_class_can_register_an_admin_menu_via_the_fluent_api()
    {
        // This test should be reimplemented as an actual integration test that hits the database
        $this->markTestIncomplete();

        WP_Mock::wpFunction('is_admin', [
            'times'  => 1,
            'return' => true,
        ]);

        $this->app->make(AdminMenus::class)
            ->addMenuPageCalled('Test Menu Page')
            ->withMenuTitle('Test Menu Title')
            ->withSettings(['test-menu-setting'])
            ->restrictedToCapability('administrator')
            ->withSlug('test-menu-slug')
            ->whichRendersView('test', ['someVariable' => true])
            ->withIcon('logo.png')
            ->add();
    }

    /** @test */
    public function the_admin_menus_class_can_render_a_view()
    {
        ob_start();

        $this->app->make(AdminMenus::class)
            ->render('test');

        ob_end_clean();
    }
}
