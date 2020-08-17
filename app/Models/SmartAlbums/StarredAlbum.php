<?php

declare(strict_types=1);

namespace App\Models\SmartAlbums;

use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\SmartAlbums\StarredAlbum.
 *
 * @property \App\Models\User $owner
 * @property \App\Models\Album $parent
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Photo[] $photos
 * @property int|null $photos_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $shared_with
 * @property int|null $shared_with_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\StarredAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\StarredAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\StarredAlbum query()
 * @mixin \Eloquent
 */
class StarredAlbum extends SmartAlbum
{
    public const ID = 'starred';

    public function get_title(): string
    {
        return 'starred';
    }

    public function getPhotos(): Builder
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
