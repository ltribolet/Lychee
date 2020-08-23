<?php

declare(strict_types=1);

namespace Tests\Feature\Lib;

use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;

class UsersUnitTest
{
    /**
     * List users.
     */
    public function list(FeatureTestCase &$testCase, string $result = 'true'): TestResponse
    {
        $response = $testCase->post('/api/User::List', []);
        $response->assertStatus(200);
        if ($result !== 'true') {
            $response->assertSee($result, false);
        }

        return $response;
    }

    public function init(FeatureTestCase &$testCase, string $result = 'true'): TestResponse
    {
        $response = $testCase->post('/php/index.php', []);
        $response->assertStatus(200);
        if ($result !== 'true') {
            $response->assertSee($result, false);
        }

        return $response;
    }

    /**
     * Add a new user.
     */
    public function add(
        FeatureTestCase &$testCase,
        string $username,
        string $password,
        string $upload,
        string $lock,
        string $result = 'true'
    ): void {
        $response = $testCase->post('/api/User::Create', [
            'username' => $username,
            'password' => $password,
            'upload' => $upload,
            'lock' => $lock,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Delete a user.
     */
    public function delete(FeatureTestCase &$testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/User::Delete', [
            'id' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Save modifications to a user.
     */
    public function save(
        FeatureTestCase &$testCase,
        string $id,
        string $username,
        string $password,
        string $upload,
        string $lock,
        string $result = 'true'
    ): void {
        $response = $testCase->post('/api/User::Save', [
            'id' => $id,
            'username' => $username,
            'password' => $password,
            'upload' => $upload,
            'lock' => $lock,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }
}
