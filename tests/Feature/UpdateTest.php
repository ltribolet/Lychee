<?php

namespace Tests\Feature;

use App\Models\Configs;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\TestCase;

class UpdateTest extends TestCase
{

    private function do_call($result): void
    {
        $response = $this->post('/api/Update::Apply', []);
        $response->assertOk();
        $response->assertSee($result);
    }

    public function testDoNotLogged(): void
    {
        $response = $this->get('/Update', []);
        $response->assertOk();
        $response->assertSee('false');

        $response = $this->post('/api/Update::Apply', []);
        $response->assertOk();
        $response->assertSee('false');

        $response = $this->post('/api/Update::Check', []);
        $response->assertOk();
        $response->assertSee('false');
    }

    public function testDoLogged(): void
    {
        $gitpull = Configs::get_value('allow_online_git_pull', '0');

        $session_tests = new SessionUnitTest();
        $this->actingAs($this->user);

        Configs::set('allow_online_git_pull', '0');
        $this->do_call('Error: Online updates are not allowed.');

        Configs::set('allow_online_git_pull', '1');

        $response = $this->get('/Update', []);
        $response->assertOk();

        $response = $this->post('/api/Update::Apply', []);
        $response->assertOk();

        $response = $this->post('/api/Update::Check', []);
        $response->assertOk();

        Configs::set('allow_online_git_pull', $gitpull);

        $session_tests->logout($this);
    }
}
