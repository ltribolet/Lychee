<?php

namespace Tests\Feature;

class RedirectFeatureTest extends FeatureTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testRedirection(): void
    {
        $response = $this->get('r/12345');

        $response->assertStatus(302);
        $response->assertRedirect('gallery#12345');

        $response = $this->get('r/12345/67890');

        $response->assertStatus(302);
        $response->assertRedirect('gallery#12345/67890');
    }
}
