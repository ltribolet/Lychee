<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Lib\SessionUnitTest;

class PhotosRotateFeatureTest extends FeatureTestCase
{
    public function testRotate(): void
    {
        $photos_tests = new PhotosUnitTest();
        $session_tests = new SessionUnitTest();

        $this->actingAs($this->user);

        /*
        * Make a copy of the image because import deletes the file and we want to be
        * able to use the test on a local machine and not just in CI.
        */
        \copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');

        $file = new UploadedFile('public/uploads/import/night.jpg', 'night.jpg', 'image/jpg', null, true);

        $id = $photos_tests->upload($this, $file);

        $photos_tests->get($this, $id, 'true');

        $response = $photos_tests->get($this, $id, 'true');
        /*
        * Check some Exif data
        */
        $response->assertJson([
            'width' => '1400',
            'height' => '788',
            'id' => $id,
            'size' => '528.1 KB',
            'small_dim' => '640x360',
            'medium_dim' => '',
        ]);

        $editor_enabled_value = Configs::get_value('editor_enabled');
        Configs::set('editor_enabled', '0');
        $response = $this->post('/api/PhotoEditor::rotate', [
            'photoID' => $id,
            'direction' => 1,
        ]);
        $response->assertStatus(200);
        $response->assertSee('false', false);

        Configs::set('editor_enabled', '1');
        $photos_tests->rotate($this, '-1', 1, 'false');
        $photos_tests->rotate($this, $id, 'asdq', 'false', 302);
        $photos_tests->rotate($this, $id, '2', 'false');
        $photos_tests->rotate($this, $id, 1);

        /*
        * Check some Exif data
        */
        $response = $photos_tests->get($this, $id, 'true');
        $response->assertJson([
            'width' => '788',
            'height' => '1400',
            'id' => $id,
            // 'size' => '20.1 MB', // This changes during the image manipulation sadly.
            'small_dim' => '360x640',
            'medium_dim' => '',
        ]);

        $photos_tests->rotate($this, $id, -1);

        /*
        * Check some Exif data
        */
        $response = $photos_tests->get($this, $id, 'true');
        $response->assertJson([
            'width' => '1400',
            'height' => '788',
            'id' => $id,
            // 'size' => '20.1 MB', // This changes during the image manipulation sadly.
            'small_dim' => '640x360',
            'medium_dim' => '',
        ]);

        $photos_tests->delete($this, $id, 'true');

        // reset
        Configs::set('editor_enabled', $editor_enabled_value);

        $session_tests->logout($this);
    }
}
