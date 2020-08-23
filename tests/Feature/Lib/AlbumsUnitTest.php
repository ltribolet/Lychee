<?php

declare(strict_types=1);

namespace Tests\Feature\Lib;

use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;

class AlbumsUnitTest
{
    /**
     * Add an album.
     */
    public function add(FeatureTestCase $testCase, int $parent_id, string $title, string $result = 'true'): string
    {
        $response = $testCase->post('/api/Album::add', [
            'title' => $title,
            'parent_id' => $parent_id,
        ]);
        $response->assertStatus(200);
        if ($result === 'true') {
            $response->assertDontSee('false');
        } else {
            $response->assertSee($result, false);
        }

        return $response->getContent();
    }

    /**
     * Move albums.
     *
     * @param string   $result
     */
    public function move(FeatureTestCase $testCase, string $ids, string $to, bool $result = false): string
    {
        $response = $testCase->post('/api/Album::move', [
            'albumIDs' => $to . ',' . $ids,
        ]);
        $response->assertStatus(200);
        if ($result) {
            $response->assertDontSee('false');
        } else {
            $response->assertSee($result, false);
        }

        return $response->getContent();
    }

    /**
     * Get all albums.
     */
    public function get_all(FeatureTestCase $testCase, string $result = 'true'): TestResponse
    {
        $response = $testCase->post('/api/Albums::get', []);
        $response->assertOk();
        if ($result !== 'true') {
            $response->assertSee($result, false);
        }

        return $response;
    }

    /**
     * Get album by ID.
     */
    public function get(
        FeatureTestCase $testCase,
        string $id,
        string $password = '',
        string $result = 'true'
    ): TestResponse {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => $id,
            'password' => $password,
        ]);
        $response->assertOk();
        if ($result !== 'true') {
            $response->assertSee($result, false);
        }

        return $response;
    }

    public function get_public(
        FeatureTestCase $testCase,
        string $id,
        string $password = '',
        string $result = 'true'
    ): void {
        $response = $testCase->post('/api/Album::getPublic', [
            'albumID' => $id,
            'password' => $password,
        ]);
        $response->assertOk();
        $response->assertSeeText($result);
    }

    /**
     * Check if we see id in the list of all visible albums
     * /!\ results varies depending if logged in or not !
     */
    public function see_in_albums(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Albums::get', []);
        $response->assertOk();
        $response->assertSee($id, false);
    }

    /**
     * Check if we don't see id in the list of all visible albums
     * /!\ results varies depending if logged in or not !
     */
    public function dont_see_in_albums(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Albums::get', []);
        $response->assertOk();
        $response->assertDontSee($id, false);
    }

    /**
     * Change title.
     */
    public function set_title(FeatureTestCase $testCase, string $id, string $title, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Album::setTitle', [
            'albumIDs' => $id,
            'title' => $title,
        ]);
        $response->assertOk();
        $response->assertSee($result, false);
    }

    /**
     * Change description.
     */
    public function set_description(
        FeatureTestCase $testCase,
        string $id,
        string $description,
        string $result = 'true'
    ): void {
        $response = $testCase->post(
            '/api/Album::setDescription',
            [
                'albumID' => $id,
                'description' => $description,
            ]
        );
        $response->assertOk();
        $response->assertSee($result, false);
    }

    /**
     * Set the licence.
     */
    public function set_license(FeatureTestCase $testCase, string $id, string $license, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Album::setLicense', [
            'albumID' => $id,
            'license' => $license,
        ]);
        $response->assertOk();
        $response->assertSee($result, false);
    }

    public function set_public(
        FeatureTestCase $testCase,
        string $id,
        int $full_photo = 1,
        int $public = 1,
        int $visible = 1,
        int $downloadable = 1,
        int $share_button_visible = 1,
        string $result = 'true'
    ): void {
        $response = $testCase->post('/api/Album::setPublic', [
            'full_photo' => $full_photo,
            'albumID' => $id,
            'public' => $public,
            'visible' => $visible,
            'downloadable' => $downloadable,
            'share_button_visible' => $share_button_visible,
        ]);
        $response->assertOk();
        $response->assertSee($result);
    }

    /**
     * We only test for a code 200.
     */
    public function download(FeatureTestCase $testCase, string $id, string $kind = 'FULL'): void
    {
        $response = $testCase->call('GET', '/api/Album::getArchive', [
            'albumIDs' => $id,
        ]);
        $response->assertStatus(200);
    }

    /**
     * Delete.
     */
    public function delete(FeatureTestCase $testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Album::delete', ['albumIDs' => $id]);
        $response->assertOk();
        $response->assertSee($result, false);
    }

    /**
     * Test position data (Albums).
     */
    public function AlbumsGetPositionDataFull(
        FeatureTestCase $testCase,
        int $code = 200,
        string $result = 'true'
    ): TestResponse {
        $response = $testCase->post('/api/Albums::getPositionData', []);
        $response->assertStatus($code);
        if ($result !== 'true') {
            $response->assertSee($result, false);
        }

        return $response;
    }

    /**
     * Test position data (Album).
     */
    public function AlbumGetPositionDataFull(
        FeatureTestCase $testCase,
        string $id,
        int $code = 200,
        string $result = 'true'
    ): TestResponse {
        $response = $testCase->post('/api/Album::getPositionData', [
            'albumID' => $id,
            'includeSubAlbums' => 'false',
        ]);
        $response->assertStatus($code);
        if ($result !== 'true') {
            $response->assertSee($result, false);
        }

        return $response;
    }
}
