<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Album;
use Illuminate\Support\Facades\Auth;

class AlbumsPhotosService
{
    private AlbumFactory $albumFactory;

    public function __construct(AlbumFactory $albumFactory)
    {
        $this->albumFactory = $albumFactory;
    }

    /**
     * @param array<mixed> $albumIds
     *
     * @return array<string, array<string, array<string, string>>>
     */
    public function getAlbumsPhotos(array $albumIds): array
    {
        $albums = [];
        $user = Auth::user();

        foreach ($albumIds as $albumId) {
            /** @var Album $album */
            $album = $this->albumFactory->getAlbum($albumId);

            // This might not be correct given the class that is aimed to be abstracted from Auth logic
            // Maybe we need to change class name or move this somewhere else.
            if (!$album->canBeSeenBy($user)) {
                continue;
            }

            $albums[] = [
                'album' => $album,
                'content' => $this->getAlbumPhotos($album),
            ];
        }

        return $albums;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getAlbumPhotos(Album $album): array
    {
        $files = [
            'photos' => [],
            'children' => [],
        ];
        $photos = $album->getPhotos()->get();

        foreach ($photos as $photo) {
            $files['photos'][] = $photo;
        }

        if ($album->children()) {
            $files['children'] = $album->children()->get()->map(function (Album $childAlbum) {
                return [
                    'album' => $childAlbum,
                    'content' => $this->getAlbumPhotos($childAlbum),
                ];
            })->toArray();
        }

        return $files;
    }
}
