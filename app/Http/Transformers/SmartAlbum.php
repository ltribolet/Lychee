<?php

declare(strict_types=1);

namespace App\Http\Transformers;

use App\ModelFunctions\PhotoActions\Thumb;
use App\Models\SmartAlbums\SmartAlbum as SmartAlbumModel;

class SmartAlbum
{
    private SmartAlbumModel $smartAlbum;

    public function __construct(SmartAlbumModel $smartAlbum)
    {
        $this->smartAlbum = $smartAlbum;
    }

    /**
     * @return array<string>
     */
    public function toArray(): array
    {
        $thumbs = $this->getPhotosThumbs();

        return [
            'thumbs' => $thumbs['thumbs'],
            'thumbs2x' => $thumbs['thumbs2x'],
            'types' => $thumbs['types'],
            'num' => $this->smartAlbum->getPhotos()->count(),
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    private function getPhotosThumbs(): array
    {
        $thumbs = [
            'thumbs' => [],
            'thumbs2x' => [],
            'types' => [],
        ];

        return $this->smartAlbum->getThumbs()->reduce(static function (array $thumbs, Thumb $thumb) {
            $thumbs['thumbs'][] = $thumb->thumb;
            $thumbs['thumbs2x'][] = $thumb->thumb2x;
            $thumbs['types'][] = $thumb->type;

            return $thumbs;
        }, $thumbs);
    }
}
