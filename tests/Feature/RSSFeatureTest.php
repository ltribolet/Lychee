<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Configs;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;

class RSSFeatureTest extends FeatureTestCase
{
    public function testRSS0(): void
    {
        // set to 0
        Configs::set('rss_enable', '0');
        self::assertSame(Configs::get_value('rss_enable'), '0');

        // check redirection
        $response = $this->get('/feed');
        $response->assertStatus(404);
    }

    public function testRSS1(): void
    {
        // set to 0
        Configs::set('rss_enable', '1');
        Configs::set('full_photo', '0');
        self::assertSame(Configs::get_value('rss_enable'), '1');

        // check redirection
        $response = $this->get('/feed');
        $response->assertStatus(200);

        // now we start adding some stuff
        $photos_tests = new PhotosUnitTest();
        $albums_tests = new AlbumsUnitTest();

        // log as admin
        $this->actingAs($this->user);

        // create an album
        $albumID = $albums_tests->add($this, 0, 'test_album', 'true');

        // upload a picture
        \copy('tests/Feature/night.jpg', 'public/uploads/import/night.jpg');
        $file = new UploadedFile('public/uploads/import/night.jpg', 'night.jpg', 'image/jpg', null, true);
        $photoID = $photos_tests->upload($this, $file);

        // set it to public
        $photos_tests->set_public($this, $photoID);

        // try to get the RSS feed.
        $response = $this->get('/feed');
        $response->assertStatus(200);

        // set picture to private
        $photos_tests->set_public($this, $photoID);

        // move picture to album
        $photos_tests->set_album($this, $albumID, $photoID, 'true');
        $albums_tests->set_public($this, $albumID, 1, 1, 1, 1, 1, 'true');

        // try to get the RSS feed.
        $response = $this->get('/feed');
        $response->assertStatus(200);

        $albums_tests->delete($this, $albumID);
    }
}
