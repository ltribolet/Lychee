<?php

declare(strict_types=1);

namespace App\SmartAlbums;

use App\Photo;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\SmartAlbums\PublicAlbum
 *
 * @property-read \App\User $owner
 * @property-read \App\Album $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Photo[] $photos
 * @property-read int|null $photos_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $shared_with
 * @property-read int|null $shared_with_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\PublicAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\PublicAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\PublicAlbum query()
 * @mixin \Eloquent
 */
class PublicAlbum extends SmartAlbum
{
    public function get_title(): string
    {
        return 'public';
    }

    public function get_photos(): Builder
    {
        // php7.4: return Photo::public()->where(fn ($q) => $this->filter($q));
        return Photo::public()->where(function ($q) {
            return $this->filter($q);
        });
    }
}
