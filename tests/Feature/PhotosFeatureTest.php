<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as BaseCollection;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Lib\SessionUnitTest;

class PhotosFeatureTest extends FeatureTestCase
{
    /**
     * Test photo operations.
     */
    public function testUpload(): void
    {
        $photos_tests = new PhotosUnitTest();
        $albums_tests = new AlbumsUnitTest();
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

        $photos_tests->see_in_unsorted($this, $id);
        $photos_tests->see_in_recent($this, $id);
        $photos_tests->dont_see_in_shared($this, $id);
        $photos_tests->dont_see_in_favorite($this, $id);

        $photos_tests->set_title($this, $id, "Night in Ploumanac'h");
        $photos_tests->set_description($this, $id, 'A night photography');
        $photos_tests->set_star($this, $id);
        $photos_tests->set_tag($this, $id, 'night');
        $photos_tests->set_public($this, $id);
        $photos_tests->set_license($this, $id, 'WTFPL', '"Error: License not recognised!"');
        $photos_tests->set_license($this, $id, 'CC0');
        $photos_tests->set_license($this, $id, 'CC-BY-1.0');
        $photos_tests->set_license($this, $id, 'CC-BY-2.0');
        $photos_tests->set_license($this, $id, 'CC-BY-2.5');
        $photos_tests->set_license($this, $id, 'CC-BY-3.0');
        $photos_tests->set_license($this, $id, 'CC-BY-4.0');
        $photos_tests->set_license($this, $id, 'CC-BY-ND-1.0');
        $photos_tests->set_license($this, $id, 'CC-BY-ND-2.0');
        $photos_tests->set_license($this, $id, 'CC-BY-ND-2.5');
        $photos_tests->set_license($this, $id, 'CC-BY-ND-3.0');
        $photos_tests->set_license($this, $id, 'CC-BY-ND-4.0');
        $photos_tests->set_license($this, $id, 'CC-BY-SA-1.0');
        $photos_tests->set_license($this, $id, 'CC-BY-SA-2.0');
        $photos_tests->set_license($this, $id, 'CC-BY-SA-2.5');
        $photos_tests->set_license($this, $id, 'CC-BY-SA-3.0');
        $photos_tests->set_license($this, $id, 'CC-BY-SA-4.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-1.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-2.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-2.5');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-3.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-4.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-ND-1.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-ND-2.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-ND-2.5');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-ND-3.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-ND-4.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-SA-1.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-SA-2.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-SA-2.5');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-SA-3.0');
        $photos_tests->set_license($this, $id, 'CC-BY-NC-SA-4.0');
        $photos_tests->set_license($this, $id, 'reserved');

        $photos_tests->see_in_favorite($this, $id);
        $photos_tests->see_in_shared($this, $id);
        $response = $photos_tests->get($this, $id, 'true');
        $photos_tests->download($this, $id, 'FULL');

        /*
         * Check some Exif data
         */
        $response->assertJson([
            'description' => 'A night photography',
            'width' => 1400,
            'height' => 788,
            'id' => $id,
            'size' => '528.1 KB',
            'iso' => '200',
            'aperture' => 'f/5.6',
            'make' => 'OLYMPUS IMAGING CORP.',
            'model' => 'E-M5MarkII',
            'shutter' => '1/160 s',
            'focal' => '150 mm',
            'lens' => 'OLYMPUS M.40-150mm F2.8',
            'license' => 'reserved',
            'public' => '1',
            'small_dim' => '640x360',
            'star' => '1',
            'tags' => 'night',
            'medium_dim' => '',
            'takedate' => '17 November 2019 at 14:54',
            'title' => "Night in Ploumanac'h",
            'type' => 'image/jpeg',
        ]);

        /**
         * Actually try to display the picture.
         */
        $response = $this->post('/api/Photo::getRandom', []);
        $response->assertStatus(200);

        /*
         * Erase tag
         */
        $photos_tests->set_tag($this, $id, '');

        /**
         * We now test interaction with albums.
         */
        $albumID = $albums_tests->add($this, 0, 'test_album_2');
        $photos_tests->set_album($this, '-1', $id, 'false');
        $photos_tests->set_album($this, $albumID, $id, 'true');
        $albums_tests->download($this, $albumID);
        $photos_tests->dont_see_in_unsorted($this, $id);

        $photos_tests->duplicate($this, $id, 'true');
        $response = $albums_tests->get($this, $albumID, '', 'true');
        $content = $response->getContent();
        $array_content = \json_decode($content);
        self::assertSame(2, \count($array_content->photos));

        $ids = [];
        $ids[0] = $array_content->photos[0]->id;
        $ids[1] = $array_content->photos[1]->id;
        $photos_tests->delete($this, $ids[0], 'true');
        $photos_tests->get($this, $id[0], 'false');

        $photos_tests->dont_see_in_recent($this, $ids[0]);
        $photos_tests->dont_see_in_unsorted($this, $ids[1]);

        $albums_tests->set_public($this, $albumID, 1, 1, 1, 1, 1, 'true');

        /**
         * Actually try to display the picture.
         */
        $response = $this->post('/api/Photo::getRandom', []);
        $response->assertStatus(200);

        // delete the picture after displaying it
        $photos_tests->delete($this, $ids[1], 'true');
        $photos_tests->get($this, $id[1], 'false');
        $response = $albums_tests->get($this, $albumID, '', 'true');
        $content = $response->getContent();
        $array_content = \json_decode($content);
        self::assertFalse($array_content->photos);

        // save initial value
        $init_config_value = Configs::get_value('gen_demo_js');

        // set to 0
        Configs::set('gen_demo_js', '1');
        self::assertSame(Configs::get_value('gen_demo_js'), '1');

        // check redirection
        $response = $this->get('/demo');
        $response->assertStatus(200);
        $response->assertViewIs('demo');

        // set back to initial value
        Configs::set('gen_demo_js', $init_config_value);

        $albums_tests->delete($this, $albumID);

        $response = $this->get('/api/Photo::clearSymLink');
        $response->assertOk();
        $response->assertSee('true');

        $session_tests->logout($this);
    }

    public function testTrueNegative(): void
    {
        $photos_tests = new PhotosUnitTest();
        $session_tests = new SessionUnitTest();

        $this->actingAs($this->user);

        $photos_tests->wrong_upload($this);
        $photos_tests->wrong_upload2($this);
        $photos_tests->get($this, '-1', 'false');
        $photos_tests->set_description($this, '-1', 'test', 'false');
        $photos_tests->set_public($this, '-1', 'false');
        $photos_tests->set_album($this, '-1', '-1', 'false');
        $photos_tests->set_license($this, '-1', 'CC0', 'false');

        $session_tests->logout($this);
    }

    public function testUpload2(): void
    {
        // save initial value
        $init_config_value1 = Configs::get_value('SL_enable');
        $init_config_value2 = Configs::get_value('SL_for_admin');

        // set to 0
        Configs::set('SL_enable', '1');
        Configs::set('SL_for_admin', '1');
        self::assertSame(Configs::get_value('SL_enable'), '1');
        self::assertSame(Configs::get_value('SL_for_admin'), '1');

        // just redo the test above :'D
        $this->testUpload();

        // set back to initial value
        Configs::set('SL_enable', $init_config_value1);
        Configs::set('SL_for_admin', $init_config_value2);
    }

    public function testImport(): void
    {
        $photos_tests = new PhotosUnitTest();
        $albums_tests = new AlbumsUnitTest();
        $session_tests = new SessionUnitTest();

        $this->actingAs($this->user);

        // save initial value
        $init_config_value = Configs::get_value('import_via_symlink');

        // enable import via symlink option
        Configs::set('import_via_symlink', '1');
        self::assertSame(Configs::get_value('import_via_symlink'), '1');

        $num_before_import = Photo::recent()->count();

        // upload the photo
        \copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');
        $photos_tests->import($this, \base_path('public/uploads/import/'));

        // check if the file is still there (without symlinks the photo would have been deleted)
        self::assertSame(true, \file_exists('public/uploads/import/night.jpg'));

        $response = $albums_tests->get($this, 'recent', '', 'true');
        $content = $response->getContent();
        $array_content = \json_decode($content);
        $photos = new BaseCollection($array_content->photos);
        self::assertSame(Photo::recent()->count(), $photos->count());
        $ids = $photos->skip($num_before_import)->implode('id', ',');
        $photos_tests->delete($this, $ids, 'true');

        self::assertSame($num_before_import, Photo::recent()->count());

        // set back to initial value
        Configs::set('import_via_symlink', $init_config_value);

        $session_tests->logout($this);
    }
}
