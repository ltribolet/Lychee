<?php

namespace Tests\Feature\app\Services;

use App\Models\Album;
use App\Services\AlbumFactory;
use Tests\Feature\FeatureTestCase;

class AlbumFactoryFeatureTest extends FeatureTestCase
{
    public function testFactoryForRegularAlbum()
    {
        $expectedAlbum = \factory(Album::class)->create(['id' => 999]);

        $albumFactory = new AlbumFactory();
        $album = $albumFactory->getAlbum($expectedAlbum->id);

        self::assertSame($expectedAlbum->id, $album->id);
        self::assertSame($expectedAlbum->title, $album->title);
    }
}
