<?php

declare(strict_types=1);

namespace App\Models\SmartAlbums;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\SmartAlbums\PublicAlbum.
 *
 * @property \App\Models\User $owner
 * @property \App\Models\Album $parent
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Photo[] $photos
 * @property int|null $photos_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $shared_with
 * @property int|null $shared_with_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\PublicAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\PublicAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\PublicAlbum query()
 * @mixin \Eloquent
 */
class PublicAlbum extends SmartAlbum
{
    public const ID = 'public';

    public function get_title(): string
    {
        return 'public';
    }

    public function getPhotos(): Builder
    {
        // php7.4: return Photo::public()->where(fn ($q) => $this->filter($q));
        return Photo::public()->where(function ($q) {
            return $this->filter($q);
        });
    }
}
