<?php

class BasicRequestTest extends FrameworkTestCase
{
    /** @test */
    public function the_site_will_load_while_the_plugin_is_enabled()
    {
        $this->visit('/');

        $this->assertResponseOk();

        $this->seePageIs('/');
    }
}
