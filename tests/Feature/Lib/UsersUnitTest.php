<?php

namespace Tests\Feature\Lib;

use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;

class UsersUnitTest
{
    /**
     * List users.
     *
     * @param FeatureTestCase $testCase
     * @param string   $result
     *
     * @return TestResponse
     */
    public function list(FeatureTestCase &$testCase, string $result = 'true')
    {
        $response = $testCase->post('/api/User::List', []);
        $response->assertStatus(200);
        if ($result !== 'true') {
            $response->assertSee($result, false);
        }

        return $response;
    }

    /**
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $result
     *
     * @return TestResponse
     */
    public function init(FeatureTestCase &$testCase, string $result = 'true')
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
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $username
     * @param string   $password
     * @param string   $upload
     * @param string   $lock
     * @param string   $result
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
     *
     * @param FeatureTestCase $testCase
     * @param string   $id
     * @param string   $result
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
     *
     * @param FeatureTestCase $testCase
     * @param string   $id
     * @param string   $username
     * @param string   $password
     * @param string   $upload
     * @param string   $lock
     * @param string   $result
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
