<?php

namespace Tests\Unit\app\Services;

use App\Models\SmartAlbums\PublicAlbum;
use App\Models\SmartAlbums\RecentAlbum;
use App\Models\SmartAlbums\StarredAlbum;
use App\Models\SmartAlbums\UnsortedAlbum;
use App\Services\AlbumFactory;
use PHPUnit\Framework\TestCase;

class AlbumFactoryTest extends TestCase
{
    /**
     * @param string $albumId
     * @param string $expected
     *
     * @dataProvider smartAlbumProvider
     */
    public function testFactoryForSmartAlbum(string $albumId, string $expected)
    {
        $albumFactory = new AlbumFactory();
        $album = $albumFactory->getAlbum($albumId);

        self::assertInstanceOf($expected, $album);
    }

    public function smartAlbumProvider(): array
    {
        return [
            [PublicAlbum::ID, PublicAlbum::class],
            [RecentAlbum::ID, RecentAlbum::class],
            [StarredAlbum::ID, StarredAlbum::class],
            [UnsortedAlbum::ID, UnsortedAlbum::class],
        ];
    }
}
