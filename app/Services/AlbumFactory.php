<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Album;
use App\Models\SmartAlbums\PublicAlbum;
use App\Models\SmartAlbums\RecentAlbum;
use App\Models\SmartAlbums\StarredAlbum;
use App\Models\SmartAlbums\UnsortedAlbum;

class AlbumFactory
{
    private static array $albumMap = [
        StarredAlbum::ID => StarredAlbum::class,
        PublicAlbum::ID => PublicAlbum::class,
        RecentAlbum::ID => RecentAlbum::class,
        UnsortedAlbum::ID => UnsortedAlbum::class,
    ];

    /**
     * @param string|int $albumId
     */
    public function getAlbum($albumId): ?Album
    {
        if (\array_key_exists($albumId, self::$albumMap)) {
            return new self::$albumMap[$albumId]();
        }

        return Album::find($albumId);
    }
}
