<?php

class BasicRequestTest extends FrameworkTestCase
{
    /** @test */
    public function the_site_will_load_while_the_plugin_is_enabled()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
