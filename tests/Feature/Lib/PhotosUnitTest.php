<?php

declare(strict_types=1);

namespace Tests\Feature\Lib;

use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;

class PhotosUnitTest
{
    /**
     * Try upload a picture.
     *
     * @return string (id of the picture)
     */
    public function upload(FeatureTestCase &$testcase, UploadedFile &$file): string
    {
        $response = $testcase->post('/api/Photo::add', [
            'albumID' => '0',
            '0' => $file,
        ]);
        $response->assertStatus(200);
        $response->assertDontSee('Error');

        return $response->getContent();
    }

    /**
     * Try uploading a picture without the file argument (will trigger the validate).
     */
    public function wrong_upload(FeatureTestCase &$testcase): void
    {
        $response = $testcase->post('/api/Photo::add', [
            'albumID' => '0',
        ]);
        $response->assertStatus(302);
    }

    /**
     * Try uploading a picture without the file type (will trigger the hasfile).
     */
    public function wrong_upload2(FeatureTestCase &$testcase): void
    {
        $response = $testcase->post('/api/Photo::add', [
            'albumID' => '0',
            '0' => '1',
        ]);
        $response->assertStatus(200);
        $response->assertSee('"Error: missing files"', false);
    }

    /**
     * Get a photo given a photo id.
     */
    public function get(FeatureTestCase $testCase, string $photo_id, string $result = 'true'): TestResponse
    {
        $response = $testCase->post('/api/Photo::get', [
            'photoID' => $photo_id,
        ]);
        $response->assertStatus(200);
        if ($result !== 'true') {
            $response->assertSee($result, false);
        }

        return $response;
    }

    /**
     * is ID visible in unsorted ?
     */
    public function see_in_unsorted(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'unsorted',
        ]);
        $response->assertStatus(200);
        $response->assertSee($id, false);
    }

    /**
     * is ID NOT visible in unsorted ?
     */
    public function dont_see_in_unsorted(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'unsorted',
        ]);
        $response->assertStatus(200);
        $response->assertDontSee($id, false);
    }

    /**
     * is ID visible in recent ?
     */
    public function see_in_recent(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'recent',
        ]);
        $response->assertStatus(200);
        $response->assertSee($id, false);
    }

    /**
     * is ID NOT visible in recent ?
     */
    public function dont_see_in_recent(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'recent',
        ]);
        $response->assertStatus(200);
        $response->assertDontSee($id, false);
    }

    /**
     * is ID visible in shared ?
     */
    public function see_in_shared(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'public',
        ]);
        $response->assertStatus(200);
        $response->assertSee($id, false);
    }

    /**
     * is ID NOT visible in shared ?
     */
    public function dont_see_in_shared(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'public',
        ]);
        $response->assertStatus(200);
        $response->assertDontSee($id, false);
    }

    /**
     * is ID visible in favorite ?
     */
    public function see_in_favorite(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'starred',
        ]);
        $response->assertStatus(200);
        $response->assertSee($id, false);
    }

    /**
     * is ID NOT visible in favorite ?
     */
    public function dont_see_in_favorite(FeatureTestCase $testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'starred',
        ]);
        $response->assertStatus(200);
        $response->assertDontSee($id, false);
    }

    /**
     * Set Title.
     */
    public function set_title(FeatureTestCase $testCase, string $id, string $title, string $result = 'true'): void
    {
        /**
         * Try to set the title.
         */
        $response = $testCase->post('/api/Photo::setTitle', [
            'title' => $title,
            'photoIDs' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Set Description.
     */
    public function set_description(
        FeatureTestCase $testCase,
        string $id,
        string $description,
        string $result = 'true'
    ): void {
        /**
         * Try to set the description.
         */
        $response = $testCase->post('/api/Photo::setDescription', [
            'description' => $description,
            'photoID' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Set Star.
     */
    public function set_star(FeatureTestCase $testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::setStar', [
            'photoIDs' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Set tags.
     */
    public function set_tag(FeatureTestCase $testCase, string $id, string $tags, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::setTags', [
            'photoIDs' => $id,
            'tags' => $tags,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Set public.
     */
    public function set_public(FeatureTestCase $testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::setPublic', [
            'photoID' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Set license.
     */
    public function set_license(FeatureTestCase $testCase, string $id, string $license, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::setLicense', [
            'photoID' => $id,
            'license' => $license,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Set Album.
     */
    public function set_album(FeatureTestCase $testCase, string $album_id, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::setAlbum', [
            'photoIDs' => $id,
            'albumID' => $album_id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Duplicate a picture.
     */
    public function duplicate(FeatureTestCase $testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::duplicate', [
            'photoIDs' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * We only test for a code 200.
     */
    public function download(FeatureTestCase $testCase, string $id, string $kind = 'FULL'): void
    {
        $response = $testCase->call('GET', '/api/Photo::getArchive', [
            'photoIDs' => $id,
            'kind' => $kind,
        ]);
        $response->assertStatus(200);
    }

    /**
     * Delete a picture.
     */
    public function delete(FeatureTestCase $testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::delete', [
            'photoIDs' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Import a picture.
     */
    public function import(
        FeatureTestCase $testCase,
        string $path,
        string $delete_imported = '0',
        string $album_id = '0',
        string $result = 'true'
    ): string {
        $response = $testCase->post('/api/Import::server', [
            'function' => 'Import::server',
            'albumID' => $album_id,
            'path' => $path,
            'delete_imported' => $delete_imported,
        ]);
        $response->assertStatus(200);
        $response->assertSee('');

        return $response->streamedContent();
    }

    /**
     * @param mixed $direction
     */
    public function rotate(
        FeatureTestCase $testCase,
        string $id,
        $direction,
        string $result = 'true',
        int $code = 200
    ): void {
        $response = $testCase->post('/api/PhotoEditor::rotate', [
            'photoID' => $id,
            'direction' => $direction,
        ]);
        $response->assertStatus($code);
        if ($code === 200) {
            $response->assertSee($result, false);
        }
    }
}
