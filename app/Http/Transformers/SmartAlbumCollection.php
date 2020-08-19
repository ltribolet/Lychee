<?php

declare(strict_types=1);

namespace App\Http\Transformers;

use App\Models\SmartAlbums\SmartAlbum as SmartAlbumModel;
use Illuminate\Support\Collection;

class SmartAlbumCollection
{
    /**
     * @return array<array<string>>
     */
    public static function transform(Collection $collection): array
    {
        return $collection->mapWithKeys(function (SmartAlbumModel $smartAlbum) {
            return [$smartAlbum->get_title() => (new SmartAlbum($smartAlbum))->toArray()];
        })->toArray();
    }
}
