<?php

declare(strict_types=1);

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;

class InstallFeatureTest extends FeatureTestCase
{
    /**
     * Testing the Login interface.
     */
    public function testInstall(): void
    {
        @\unlink(\base_path('installed.log'));
        $response = $this->get('install/');
        $response->assertStatus(200);

        /*
         * Clearing things up. We could do an Artisan migrate but this is more efficient.
         */
        $tables = [
            'sym_links',
            'photos',
            'configs',
            'logs',
            'migrations',
            'page_contents',
            'pages',
            'user_album',
            'users',
            'albums',
        ];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        /**
         * No database: we should be redirected to install: default case.
         */
        $response = $this->get('/');
        $response->assertStatus(307);
        $response->assertRedirect('install/');

        /**
         * Check the welcome page.
         */
        $response = $this->get('install/');
        $response->assertStatus(200);
        $response->assertViewIs('install.welcome');

        /**
         * Check the requirements page.
         */
        $response = $this->get('install/req');
        $response->assertStatus(200);
        $response->assertViewIs('install.requirements');

        /**
         * Check the permissions page.
         */
        $response = $this->get('install/perm');
        $response->assertStatus(200);
        $response->assertViewIs('install.permissions');

        /**
         * Check the env page.
         */
        $response = $this->get('install/env');
        $response->assertStatus(200);
        $response->assertViewIs('install.env');

        $env = \file_get_contents(\base_path('.env'));

        /**
         * POST '.env' the env page.
         */
        $response = $this->post('install/env', ['envConfig' => $env]);
        $response->assertStatus(200);
        $response->assertViewIs('install.env');

        /**
         * apply migration.
         */
        $response = $this->get('install/migrate');
        $response->assertStatus(200);
        $response->assertViewIs('install.migrate');

        /**
         * We now should be redirected.
         */
        $response = $this->get('install/');
        $response->assertStatus(307);
        $response->assertRedirect('/');

        /**
         * We now should NOT be redirected.
         */
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
