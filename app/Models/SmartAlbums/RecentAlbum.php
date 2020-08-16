<?php

declare(strict_types=1);

namespace App\Models\SmartAlbums;

use App\ModelFunctions\AlbumsFunctions;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\SmartAlbums\RecentAlbum.
 *
 * @property \App\Models\User $owner
 * @property \App\Models\Album $parent
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Photo[] $photos
 * @property int|null $photos_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $shared_with
 * @property int|null $shared_with_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\RecentAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\RecentAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\RecentAlbum query()
 * @mixin \Eloquent
 */
class RecentAlbum extends SmartAlbum
{
    public const ID = 'recent';

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

    public function getAvailablePhotos(): Builder
    {
        if (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->upload)) {
            return Photo::select_recent(Photo::ownedBy(Auth::user()->id));
        }

        $albumsFunctions = App::make(AlbumsFunctions::class);

        return Photo::select_recent(Photo::whereIn('album_id', $albumsFunctions->getPublicAlbumsId()));
    }
}
