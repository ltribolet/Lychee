<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Configs;

class IndexFeatureTest extends FeatureTestCase
{
    /**
     * Testing the Login interface.
     */
    public function testHome(): void
    {
        /**
         * check if we can actually get a nice answer.
         */
        $response = $this->get('/');
        $response->assertOk();

        $response = $this->post('/php/index.php', []);
        $response->assertOk();

        $response = $this->post('/api/Albums::get', []);
        $response->assertOk();
    }

    /**
     * More tests.
     */
    public function testPhpInfo(): void
    {
        // we don't want a non admin to access this
        $response = $this->get('/phpinfo');
        $response->assertStatus(200);
        $response->assertDontSeeText('php');
        $response->assertSeeText('false');
    }

    public function testLandingPage(): void
    {
        $landing_on_off = Configs::get_value('landing_page_enable', '0');
        Configs::set('landing_page_enable', '1');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('landing');

        $response = $this->get('/gallery');
        $response->assertStatus(200);
        $response->assertViewIs('gallery');

        Configs::set('landing_page_enable', $landing_on_off);
    }
}
