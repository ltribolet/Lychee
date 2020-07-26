<?php

declare(strict_types=1);

namespace App\SmartAlbums;

use App\Album;
use App\Configs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;

class SmartAlbum extends Album
{
    /**
     * @var int
     */
    public string $id = '';

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
     * @var AlbumFunctions
     */
    protected AlbumFunctions $albumFunctions;

    /**
     * @var BaseCollection[int]
     */
    protected BaseCollection $albumIds;

    /**
     * @var SessionFunctions
     */
    protected $sessionFunctions;

    public function __construct(AlbumFunctions $albumFunctions, SessionFunctions $sessionFunctions)
    {
        parent::__construct();

        $this->albumFunctions = $albumFunctions;
        $this->sessionFunctions = $sessionFunctions;
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
        if (!$this->sessionFunctions->is_admin()) {
            $query = $query->whereIn('album_id', $this->albumIds)
                ->orWhere('public', '=', 1);
        }

        if ($this->sessionFunctions->is_logged_in() && $this->sessionFunctions->id() > 0) {
            $query = $query->orWhere('owner_id', '=', $this->sessionFunctions->id());
        }

        return $query;
    }

    public function is_full_photo_visible(): bool
    {
        return false;
    }

    public function str_parent_id(): void
    {
    }

    public function is_downloadable(): bool
    {
        return Configs::get_value('downloadable', '0') === '1';
    }

    public function is_share_button_visible(): string
    {
        return Configs::get_value('share_button_visible', '0');
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
