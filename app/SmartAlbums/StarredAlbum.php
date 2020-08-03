<?php

declare(strict_types=1);

namespace App\SmartAlbums;

use App\Configs;
use App\Photo;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\SmartAlbums\StarredAlbum.
 *
 * @property \App\User $owner
 * @property \App\Album $parent
 * @property \Illuminate\Database\Eloquent\Collection|\App\Photo[] $photos
 * @property int|null $photos_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\User[] $shared_with
 * @property int|null $shared_with_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\StarredAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\StarredAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmartAlbums\StarredAlbum query()
 * @mixin \Eloquent
 */
class StarredAlbum extends SmartAlbum
{
    public function get_title(): string
    {
        return 'starred';
    }

    public function get_photos(): Builder
    {
        // php7.4: return Photo::stars()->where(fn ($q) => $this->filter($q));
        return Photo::stars()->where(function ($q) {
            return $this->filter($q);
        });
    }

    public function is_public(): bool
    {
        return Configs::get_value('public_starred', '0') === '1';
    }
}
