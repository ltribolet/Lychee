<?php

declare(strict_types=1);

namespace Tests\Feature\Lib;

use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;

class SessionUnitTest
{
    /**
     * Logging in.
     */
    public function login(FeatureTestCase $testCase, string $username, string $password, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Session::login', [
            'user' => $username,
            'password' => $password,
        ]);
        $response->assertOk();
        $response->assertSee($result, false);
    }

    public function init(FeatureTestCase $testCase, string $result = 'true'): TestResponse
    {
        $response = $testCase->post('/api/Session::init', []);
        $response->assertStatus(200);
        if ($result !== 'true') {
            $response->assertSee($result, false);
        }

        return $response;
    }

    /**
     * Logging out.
     */
    public function logout(FeatureTestCase $testCase): void
    {
        $response = $testCase->post('/api/Session::logout');
        $response->assertOk();
        $response->assertSee('true');
    }

    /**
     * Set a new login and password.
     */
    public function set_new(FeatureTestCase $testCase, string $login, string $password, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Settings::setLogin', [
            'username' => $login,
            'password' => $password,
        ]);
        $response->assertOk();
        $response->assertSee($result, false);
    }

    /**
     * Set a new login and password.
     */
    public function set_old(
        FeatureTestCase $testCase,
        string $login,
        string $password,
        string $oldUsername,
        string $oldPassword,
        string $result = 'true'
    ): void {
        $response = $testCase->post('/api/Settings::setLogin', [
            'username' => $login,
            'password' => $password,
            'oldUsername' => $oldUsername,
            'oldPassword' => $oldPassword,
        ]);
        $response->assertOk();
        $response->assertSee($result, false);
    }
}
