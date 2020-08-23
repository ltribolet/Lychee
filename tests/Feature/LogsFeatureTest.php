<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Logs;
use Tests\Feature\Lib\SessionUnitTest;

class LogsFeatureTest extends FeatureTestCase
{
    /**
     * Test log handling.
     */
    public function testLogs(): void
    {
        $session_tests = new SessionUnitTest();

        $response = $this->get('/Logs');
        $response->assertOk();
        $response->assertSeeText('false');

        // set user as admin
        $this->actingAs($this->user);

        Logs::notice(__METHOD__, (string) __LINE__, 'test');
        $response = $this->get('/Logs');
        $response->assertOk();
        $response->assertDontSeeText('false');
        $response->assertViewIs('logs.list');

        $session_tests->logout($this);
    }

    public function testApiLogs(): void
    {
        $response = $this->post('/api/Logs');
        $response->assertStatus(200); // code 200 something

        // we may decide to change for another out there so
    }

    public function testClearLogs(): void
    {
        $response = $this->post('/api/Logs::clearNoise');
        $response->assertOk();
        $response->assertSeeText('false');

        $response = $this->post('/api/Logs::clear');
        $response->assertOk();
        $response->assertSeeText('false');

        // set user as admin
        $session_tests = new SessionUnitTest();
        $this->actingAs($this->user);

        $response = $this->post('/api/Logs::clearNoise');
        $response->assertOk();
        $response->assertSeeText('Log Noise cleared');

        $response = $this->post('/api/Logs::clear');
        $response->assertOk();
        $response->assertSeeText('Log cleared');

        $response = $this->get('/Logs');
        $response->assertOk();
        $response->assertSeeText('Everything looks fine, Lychee has not reported any problems!');

        $session_tests->logout($this);
    }
}
