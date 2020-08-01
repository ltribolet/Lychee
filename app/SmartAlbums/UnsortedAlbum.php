<?php

declare(strict_types=1);

namespace App\SmartAlbums;

use App\Photo;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\SmartAlbums\UnsortedAlbum
 *
 * @property-read \App\User $owner
 * @property-read \App\Album $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Photo[] $photos
 * @property-read int|null $photos_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $shared_with
 * @property-read int|null $shared_with_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\UnsortedAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\UnsortedAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\UnsortedAlbum query()
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

    public function is_public(): bool
    {
        return false;
    }
}
