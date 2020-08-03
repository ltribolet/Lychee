<?php

declare(strict_types=1);

namespace App\SmartAlbums;

use App\Configs;
use App\Photo;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\SmartAlbums\RecentAlbum.
 *
 * @property \App\User $owner
 * @property \App\Album $parent
 * @property \Illuminate\Database\Eloquent\Collection|\App\Photo[] $photos
 * @property int|null $photos_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\User[] $shared_with
 * @property int|null $shared_with_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\RecentAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\RecentAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\RecentAlbum query()
 * @mixin \Eloquent
 */
class RecentAlbum extends SmartAlbum
{
    public function get_title(): string
    {
        return 'recent';
    }

    public function get_photos(): Builder
    {
        // php7.4: return Photo::recent()->where(fn ($q) => $this->filter($q));
        return Photo::recent()->where(function ($q) {
            return $this->filter($q);
        });
    }

    public function is_public(): bool
    {
        return Configs::get_value('public_recent', '0') === '1';
    }
}
