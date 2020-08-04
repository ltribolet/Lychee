<?php

declare(strict_types=1);

namespace App\Models\SmartAlbums;

use App\Models\Album;
use App\Models\Configs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\SmartAlbums\SmartAlbum.
 *
 * @property \App\Models\User $owner
 * @property \App\Models\Album $parent
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Photo[] $photos
 * @property int|null $photos_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $shared_with
 * @property int|null $shared_with_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\SmartAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\SmartAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmartAlbums\SmartAlbum query()
 * @mixin \Eloquent
 */
class SmartAlbum extends Album
{
    /**
     * @var int
     */
    public int $id = 0;

    /**
     * @var string
     */
    public string $title = '';

    /**
     * @var string
     */
    public string $description = '';

    /**
     * @var bool
     */
    public string $public = '';

    /**
     * @var bool
     */
    public bool $visible_hidden = false;

    /**
     * @var Carbon
     */
    public Carbon $created_at;

    /**
     * fake password string.
     *
     * @var string
     */
    public string $password = '';

    /**
     * @var BaseCollection[int]
     */
    protected BaseCollection $albumIds;

    public function __construct()
    {
        parent::__construct();

        $this->albumIds = new BaseCollection();
        $this->created_at = new Carbon();
    }

    public function get_title(): string
    {
        return 'undefined';
    }

    /**
     * Set a restriction on the available albums.
     *
     * @param BaseCollection[int] $albumIds
     */
    public function setAlbumIDs(BaseCollection $albumIds): void
    {
        $this->albumIds = $albumIds;
    }

    public function filter(Builder $query): Builder
    {
        if (!Auth::check()) {
            $query = $query->whereIn('album_id', $this->albumIds)
                ->orWhere('public', '=', 1);
        }

        if (\optional(Auth::user())->user_id > 0) {
            $query = $query->orWhere('owner_id', '=', Auth::user()->user_id);
        }

        return $query;
    }

    public function is_full_photo_visible(): bool
    {
        return false;
    }

    public function str_parent_id(): string
    {
        return '';
    }

    public function is_downloadable(): bool
    {
        return Configs::get_value('downloadable', '0') === '1';
    }

    public function is_share_button_visible(): bool
    {
        return (bool) Configs::get_value('share_button_visible', '0');
    }

    // Parse date
    public function str_min_takestamp(): string
    {
        return '';
    }

    public function str_max_takestamp(): string
    {
        return '';
    }

    public function get_license(): string
    {
        return 'none';
    }

    public function is_public(): bool
    {
        return false;
    }

    public function children(): void
    {
    }
}
