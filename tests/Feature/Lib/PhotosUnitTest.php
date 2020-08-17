<?php

namespace Tests\Feature\Lib;

use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;

class PhotosUnitTest
{
    /**
     * Try upload a picture.
     *
     * @param FeatureTestCase     $testcase
     * @param UploadedFile $file
     *
     * @return string (id of the picture)
     */
    public function upload(FeatureTestCase &$testcase, UploadedFile &$file)
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
     *
     * @param \Tests\Feature\FeatureTestCase $testcase
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
     *
     * @param \Tests\Feature\FeatureTestCase $testcase
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
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $photo_id
     * @param string   $result
     *
     * @return TestResponse
     */
    public function get(FeatureTestCase &$testCase, string $photo_id, string $result = 'true')
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
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     */
    public function see_in_unsorted(FeatureTestCase &$testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'unsorted',
        ]);
        $response->assertStatus(200);
        $response->assertSee($id, false);
    }

    /**
     * is ID NOT visible in unsorted ?
     *
     * @param FeatureTestCase $testCase
     * @param string   $id
     */
    public function dont_see_in_unsorted(FeatureTestCase &$testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'unsorted',
        ]);
        $response->assertStatus(200);
        $response->assertDontSee($id, false);
    }

    /**
     * is ID visible in recent ?
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     */
    public function see_in_recent(FeatureTestCase &$testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'recent',
        ]);
        $response->assertStatus(200);
        $response->assertSee($id, false);
    }

    /**
     * is ID NOT visible in recent ?
     *
     * @param FeatureTestCase $testCase
     * @param string   $id
     */
    public function dont_see_in_recent(FeatureTestCase &$testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'recent',
        ]);
        $response->assertStatus(200);
        $response->assertDontSee($id, false);
    }

    /**
     * is ID visible in shared ?
     *
     * @param FeatureTestCase $testCase
     * @param string   $id
     */
    public function see_in_shared(FeatureTestCase &$testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'public',
        ]);
        $response->assertStatus(200);
        $response->assertSee($id, false);
    }

    /**
     * is ID NOT visible in shared ?
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     */
    public function dont_see_in_shared(FeatureTestCase &$testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'public',
        ]);
        $response->assertStatus(200);
        $response->assertDontSee($id, false);
    }

    /**
     * is ID visible in favorite ?
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     */
    public function see_in_favorite(FeatureTestCase &$testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'starred',
        ]);
        $response->assertStatus(200);
        $response->assertSee($id, false);
    }

    /**
     * is ID NOT visible in favorite ?
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     */
    public function dont_see_in_favorite(FeatureTestCase &$testCase, string $id): void
    {
        $response = $testCase->post('/api/Album::get', [
            'albumID' => 'starred',
        ]);
        $response->assertStatus(200);
        $response->assertDontSee($id, false);
    }

    /**
     * Set Title.
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     * @param string   $title
     * @param string   $result
     */
    public function set_title(FeatureTestCase &$testCase, string $id, string $title, string $result = 'true'): void
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
     *
     * @param FeatureTestCase $testCase
     * @param string   $id
     * @param string   $description
     * @param string   $result
     */
    public function set_description(
        FeatureTestCase &$testCase,
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
     *
     * @param FeatureTestCase $testCase
     * @param string   $id
     * @param string   $result
     */
    public function set_star(FeatureTestCase &$testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::setStar', [
            'photoIDs' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Set tags.
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     * @param string   $tags
     * @param string   $result
     */
    public function set_tag(FeatureTestCase &$testCase, string $id, string $tags, string $result = 'true'): void
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
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     * @param string   $result
     */
    public function set_public(FeatureTestCase &$testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::setPublic', [
            'photoID' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Set license.
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     * @param string   $license
     * @param string   $result
     */
    public function set_license(FeatureTestCase &$testCase, string $id, string $license, string $result = 'true'): void
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
     *
     * @param FeatureTestCase $testCase
     * @param string   $album_id
     * @param string   $id
     * @param string   $result
     */
    public function set_album(FeatureTestCase &$testCase, string $album_id, string $id, string $result = 'true'): void
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
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     * @param string   $result
     */
    public function duplicate(FeatureTestCase &$testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::duplicate', [
            'photoIDs' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * We only test for a code 200.
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $id
     * @param string   $kind
     */
    public function download(FeatureTestCase &$testCase, string $id, string $kind = 'FULL'): void
    {
        $response = $testCase->call('GET', '/api/Photo::getArchive', [
            'photoIDs' => $id,
            'kind' => $kind,
        ]);
        $response->assertStatus(200);
    }

    /**
     * Delete a picture.
     *
     * @param FeatureTestCase $testCase
     * @param string   $id
     * @param string   $result
     */
    public function delete(FeatureTestCase &$testCase, string $id, string $result = 'true'): void
    {
        $response = $testCase->post('/api/Photo::delete', [
            'photoIDs' => $id,
        ]);
        $response->assertStatus(200);
        $response->assertSee($result, false);
    }

    /**
     * Import a picture.
     *
     * @param \Tests\Feature\FeatureTestCase $testCase
     * @param string   $path
     * @param string   $delete_imported
     * @param string   $album_id
     * @param string   $result
     *
     * @return string
     */
    public function import(
        FeatureTestCase &$testCase,
        string $path,
        string $delete_imported = '0',
        string $album_id = '0',
        string $result = 'true'
    ) {
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

    public function rotate(
        FeatureTestCase &$testCase,
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
