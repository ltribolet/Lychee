<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * App\Models\Album.
 *
 * @property int $id
 * @property string $title
 * @property int $owner_id
 * @property int|null $parent_id
 * @property string $description
 * @property Carbon|null $min_takestamp
 * @property Carbon|null $max_takestamp
 * @property int $public
 * @property int $full_photo
 * @property int $visible_hidden
 * @property int $downloadable
 * @property int $share_button_visible
 * @property string|null $password
 * @property string $license
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection[Album] $children
 * @property User $owner
 * @property Album $parent
 * @property Collection[Photo] $photos
 *
 * @method static Builder|Album newModelQuery()
 * @method static Builder|Album newQuery()
 * @method static Builder|Album query()
 * @method static Builder|Album whereCreatedAt($value)
 * @method static Builder|Album whereDescription($value)
 * @method static Builder|Album whereDownloadable($value)
 * @method static Builder|Album whereShareButtonVisible($value)
 * @method static Builder|Album whereId($value)
 * @method static Builder|Album whereLicense($value)
 * @method static Builder|Album whereMaxTakestamp($value)
 * @method static Builder|Album whereMinTakestamp($value)
 * @method static Builder|Album whereOwnerId($value)
 * @method static Builder|Album whereParentId($value)
 * @method static Builder|Album wherePassword($value)
 * @method static Builder|Album wherePublic($value)
 * @method static Builder|Album whereTitle($value)
 * @method static Builder|Album whereUpdatedAt($value)
 * @method static Builder|Album whereVisibleHidden($value)
 * @mixin \Eloquent
 *
 * @property Collection|User[] $shared_with
 * @property int|null $children_count
 * @property int|null $photos_count
 * @property int|null $shared_with_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Album whereFullPhoto($value)
 */
class Album extends Model
{
    /**
     * @var array<string>
     */
    protected $dates = ['created_at', 'updated_at', 'min_takestamp', 'max_takestamp'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'public' => 'int',
        'owner_id' => 'int',
        'parent_id' => 'int',
        'visible_hidden' => 'int',
        'downloadable' => 'int',
        'share_button_visible' => 'int',
    ];

    public bool $smart = false;

    /**
     * Return the relationship between Photos and their Album.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'album_id', 'id');
    }

    /**
     * Return the list of photos.
     */
    public function getPhotos(): Builder
    {
        return $this->photos()->getQuery();
    }

    /**
     * Return the relationship between an album and its owner.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id')->withDefault([
            'id' => 0,
            'username' => 'Admin',
        ]);
    }

    /**
     * Return the relationship between an album and its sub albums.
     *
     * @return void|HasMany we set void as well because Children classes are returning void
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    /**
     * Return the relationship between a sub album and its parent.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function shared_with(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_album', 'album_id', 'user_id');
    }

    /**
     * Return whether or not public users will see the full photo.
     */
    public function is_full_photo_visible(): bool
    {
        if ($this->public) {
            return $this->full_photo === 1;
        }

        return Configs::get_value('full_photo', '1') === '1';
    }

    /**
     * Return parent_id as a string or null.
     */
    public function str_parent_id(): string
    {
        return $this->parent_id === null ? '' : (string) $this->parent_id;
    }

    /**
     * Return min_takestamp as a string or ''.
     */
    public function str_min_takestamp(): string
    {
        return $this->min_takestamp === null ? '' : $this->min_takestamp->format('M Y');
    }

    /**
     * Return min_takestamp as a string or ''.
     */
    public function str_max_takestamp(): string
    {
        return $this->max_takestamp === null ? '' : $this->max_takestamp->format('M Y');
    }

    /**
     * Return whether or not public users can download photos.
     */
    public function is_downloadable(): bool
    {
        if ($this->public) {
            return $this->downloadable === 1;
        }

        return Configs::get_value('downloadable', '0') === '1';
    }

    /**
     * Return whether or not display share button.
     */
    public function is_share_button_visible(): bool
    {
        if ($this->public) {
            return $this->share_button_visible === 1;
        }

        return Configs::get_value('share_button_visible', '0') === '1';
    }

    /**
     * Return the Album license or the default one.
     */
    public function get_license(): string
    {
        if ($this->license === 'none') {
            return Configs::get_value('default_license');
        }

        return $this->license;
    }

    /**
     * Before calling delete() to remove the album from the database
     * we need to go through each sub album and delete it.
     * Idem we also delete each pictures inside an album (recursively).
     *
     * @return bool|int
     *
     * @throws Exception
     */
    public function predelete()
    {
        $no_error = true;
        $albums = $this->children;

        foreach ($albums as $album) {
            $no_error &= $album->predelete();
            $no_error &= $album->delete();
        }

        $photos = $this->photos;
        foreach ($photos as $photo) {
            $no_error &= $photo->predelete();
            $no_error &= $photo->delete();
        }

        return $no_error;
    }

    /**
     * Return the full path of the album consisting of all its parents' titles.
     */
    public static function getFullPath(self $album): string
    {
        $title = [$album->title];
        $parentId = $album->parent_id;
        while ($parentId) {
            $parent = self::find($parentId);
            \array_unshift($title, $parent->title);
            $parentId = $parent->parent_id;
        }

        return \implode('/', $title);
    }

    public function isSmart(): bool
    {
        return $this->smart;
    }

    public function getArchiveTitle(): string
    {
        return \str_replace(Config::get('file.invalid_characters'), '', $this->title) ?: 'Untitled';
    }

    public function getAvailablePhotos(): Builder
    {
        return Photo::set_order($this->getPhotos());
    }

    public function canBeSeenBy(?User $user): bool
    {
        if ($this->public) {
            return true;
        }

        if ($user && ($user->id === $this->owner_id || $user->isAdmin())) {
            return true;
        }

        $isShared = $this->shared_with->map(function (User $user) {
            return $user->id;
        })->contains(\optional($user)->id);

        if ($isShared) {
            return true;
        }

        return false;
    }
}
