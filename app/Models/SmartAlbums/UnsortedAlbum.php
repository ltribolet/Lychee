<?php

declare(strict_types=1);

namespace App\Models\SmartAlbums;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\SmartAlbums\UnsortedAlbum.
 *
 * @property \App\Models\User $owner
 * @property \App\Models\Album $parent
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Photo[] $photos
 * @property int|null $photos_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $shared_with
 * @property int|null $shared_with_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\UnsortedAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\UnsortedAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\UnsortedAlbum query()
 * @mixin \Eloquent
 */
class UnsortedAlbum extends SmartAlbum
{
    public int $id = 0;

    public function get_title(): string
    {
        return 'unsorted';
    }

    public function get_photos(): Builder
    {
        // php7.4: return Photo::unsorted()->where(fn ($q) => $this->filter($q));
        return Photo::unsorted()->where(function ($q) {
            return $this->filter($q);
        });
    }
}
