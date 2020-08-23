<?php

declare(strict_types=1);

namespace App\ModelFunctions;

use App\ControllerFunctions\ReadAccessFunctions;
use App\ModelFunctions\AlbumActions\Cast as AlbumCast;
use App\Models\Album;
use App\Models\Configs;
use App\Models\SmartAlbums\PublicAlbum;
use App\Models\SmartAlbums\RecentAlbum;
use App\Models\SmartAlbums\StarredAlbum;
use App\Models\SmartAlbums\UnsortedAlbum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class AlbumsFunctions
{
    /**
     * @var readAccessFunctions
     */
    private $readAccessFunctions;

    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    /**
     * @var AlbumFunctions
     */
    private $albumFunctions;

    /**
     * @var SymLinkFunctions
     */
    private $symLinkFunctions;

    /**
     * AlbumFunctions constructor.
     */
    public function __construct(
        SessionFunctions $sessionFunctions,
        ReadAccessFunctions $readAccessFunctions,
        AlbumFunctions $albumFunctions,
        SymLinkFunctions $symLinkFunctions
    ) {
        $this->sessionFunctions = $sessionFunctions;
        $this->readAccessFunctions = $readAccessFunctions;
        $this->albumFunctions = $albumFunctions;
        $this->symLinkFunctions = $symLinkFunctions;
    }

    /**
     * ? Only used in AlbumsController
     * Given a list of albums, generate an array to be returned.
     *
     * @return array<mixed>
     */
    public function prepare_albums(BaseCollection $albums, BaseCollection $children): array
    {
        $return = [];
        foreach ($albums->keys() as $key) {
            $album_array = AlbumCast::toArrayWith($albums[$key], $children[$key]);

            if ($this->sessionFunctions->is_logged_in()) {
                $album_array['owner'] = $albums[$key]->owner->username;
            }

            $thumbs = $this->albumFunctions->get_thumbs($albums[$key], $children[$key]);
            $this->albumFunctions->set_thumbs($album_array, $thumbs);
            $this->albumFunctions->set_thumbs_children($album_array['albums'], $thumbs[1]);

            // Add to return
            $return[] = $album_array;
        }

        return $return;
    }

    /**
     * @param array<BaseCollection> $albums_list
     *
     * @return array<BaseCollection>
     */
    public function get_children(array $albums_list): array
    {
        $return = [];
        foreach ($albums_list as $kind => $albums) {
            $return[$kind] = new BaseCollection();

            $albums->each(function ($album, $key) use ($return, $kind): void {
                $children = new Collection();

                if ($this->readAccessFunctions->album($album) === 1) {
                    $children = $this->albumFunctions->get_children($album);
                }

                $return[$kind]->put($key, $children);
            });
        }

        return $return;
    }

    /**
     * @param array<Collection>|null $toplevel return from getToplevelAlbums()
     * @param array<Collection>|null $children
     *
     * @return array<mixed> returns an array of smart albums or false on failure
     */
    public function getSmartAlbums(?array $toplevel = null, ?array $children = null): ?array
    {
        $return = [];
        /** @var Collection[SmartAlbum] */
        $publicAlbums = null;
        $smartAlbums = new BaseCollection();
        $smartAlbums->push(new UnsortedAlbum());
        $smartAlbums->push(new StarredAlbum());
        $smartAlbums->push(new PublicAlbum());
        $smartAlbums->push(new RecentAlbum());

        $can_see_smart = $this->sessionFunctions->is_logged_in() && $this->sessionFunctions->can_upload();

        foreach ($smartAlbums as $smartAlbum) {
            if ($can_see_smart || $smartAlbum->is_public()) {
                $publicAlbums = $publicAlbums ?? $this->getPublicAlbumsId($toplevel, $children);
                $smartAlbum->setAlbumIDs($publicAlbums);
                $return[$smartAlbum->get_title()] = [];

                AlbumCast::getThumbs($return[$smartAlbum->get_title()], $smartAlbum, $this->symLinkFunctions);
            }
        }

        if (empty($return)) {
            return null;
        }

        return $return;
    }

    /**
     * @param array<Collection>|null $toplevel return from getToplevelAlbums()
     * @param array<Collection>|null $children
     *
     * @return Collection[int] of all recursive albums ID accessible by the current user from the top level
     */
    public function getPublicAlbumsId(?array $toplevel = null, ?array $children = null): ?BaseCollection
    {
        /** @var Collection[Album] */
        $toplevel = $toplevel ?? $this->getToplevelAlbums();
        if ($toplevel === null) {
            return null;
        }
        $children = $children ?? $this->get_children($toplevel);

        $kinds = ['albums', 'shared_albums'];
        $albumIDs = new BaseCollection();

        foreach ($kinds as $kind) {
            $toplevel[$kind]->each(function ($album) use (&$albumIDs): void {
                $albumIDs->push($album->id);
            });
            $children[$kind]->each(function ($child) use (&$albumIDs): void {
                $albumIDs = $albumIDs->concat($this->flatMap_id($child));
            });
        }

        return $albumIDs;
    }

    /**
     * Returns an array of top-level albums and shared albums visible to
     * the current user.
     * Note: the array may include password-protected albums that are not
     * accessible (but are visible).
     *
     * @return array<Collection>
     */
    public function getToplevelAlbums(): array
    {
        $return = [
            'albums' => new BaseCollection(),
            'shared_albums' => new BaseCollection(),
        ];

        $sortingCol = Configs::get_value('sorting_Albums_col');
        $sortingOrder = Configs::get_value('sorting_Albums_order');

        if ($this->sessionFunctions->is_logged_in()) {
            $sql = Album::with(['owner'])->where('parent_id', '=', null);

            $id = $this->sessionFunctions->id();

            if ($id > 0) {
                $sql = $sql->where(function ($query) use ($id): void {
                    $query = $query->where('owner_id', '=', $id);
                    $query = $query->orWhereIn('id', DB::table('user_album')->select('album_id')
                        ->where('user_id', '=', $id));
                    $query = $query->orWhere(function ($_query): void {
                        $_query->where('public', '=', true)->where('visible_hidden', '=', true);
                    });
                });
            }

            $sql = $sql->orderBy('owner_id', 'ASC');

            $albumCollection = $this->albumFunctions->customSort($sql, $sortingCol, $sortingOrder);

            [$return['albums'], $return['shared_albums']] = $albumCollection->partition(function ($album) use (
                $id
            ) {
                return $album->owner_id === $id;
            });
        } else {
            $return['albums'] = $this->albumFunctions->customSort(Album::where('public', '=', '1')
                ->where('visible_hidden', '=', '1')
                ->where('parent_id', '=', null), $sortingCol, $sortingOrder);
        }

        return $return;
    }

    private function flatMap_id(BaseCollection $subAlbums): BaseCollection
    {
        return $subAlbums->reduce(function ($collect, $e) {
            $collect->push($e[0]->id);

            return $collect->concat($this->flatMap_id($e[1]));
        }, new BaseCollection());
    }
}
