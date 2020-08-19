<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\AlbumCollection;
use App\Http\Transformers\SmartAlbumCollection;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\AlbumsFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Services\AlbumsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class AlbumsController extends Controller
{
    /**
     * @var AlbumFunctions
     */
    private $albumFunctions;

    /**
     * @var AlbumsFunctions
     */
    private $albumsFunctions;

    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    public function __construct(
        AlbumFunctions $albumFunctions,
        AlbumsFunctions $albumsFunctions,
        SessionFunctions $sessionFunctions
    ) {
        $this->albumFunctions = $albumFunctions;
        $this->albumsFunctions = $albumsFunctions;
        $this->sessionFunctions = $sessionFunctions;
    }

    /**
     * @deprecated
     *
     * @return array<mixed> returns an array of albums or false on failure
     */
    public function get(): array
    {
        // caching to avoid further request
        Configs::get();

        // Initialize return var
        $return = [
            'smartalbums' => null,
            'albums' => null,
            'shared_albums' => null,
        ];

        // $toplevel containts Collection[Album] accessible at the root: albums shared_albums.
        $toplevel = $this->albumsFunctions->getToplevelAlbums();
        $children = $this->albumsFunctions->get_children($toplevel);

        $return['albums'] = $this->albumsFunctions->prepare_albums($toplevel['albums'], $children['albums']);
        $return['shared_albums'] = $this->albumsFunctions->prepare_albums(
            $toplevel['shared_albums'],
            $children['shared_albums']
        );

        $return['smartalbums'] = $this->albumsFunctions->getSmartAlbums($toplevel, $children);

        return $return;
    }

    public function index(AlbumsService $service): JsonResponse
    {
        $user = Auth::user();
        $allAlbumsCollection = $service->getVisibleAlbums();

        [$albumsCollection, $smartAlbumsCollections] = $allAlbumsCollection->partition(
            function (Album $album) {
                return !$album->isSmart();
            }
        );

        [$albumsCollection, $sharedAlbumCollection] = $albumsCollection->partition(
            function (Album $album) use ($user) {
                return $album->owner_id === \optional($user)->id;
            }
        );

        return Response::json([
            'albums' => new AlbumCollection($albumsCollection),
            'shared_albums' => new AlbumCollection($sharedAlbumCollection),
            'smart_albums' => SmartAlbumCollection::transform($smartAlbumsCollections),
        ]);
    }

    /**
     * @return array<string> returns an array of photos of all albums or false on failure
     */
    public function getPositionData(): array
    {
        // caching to avoid further request
        Configs::get();

        // Initialize return var
        $return = [];

        $albumIDs = $this->albumsFunctions->getPublicAlbumsId();

        $query = Photo::with('album')->where(
            function (Builder $query) use ($albumIDs): void {
                $query->whereIn('album_id', $albumIDs);
                // Add the 'Unsorted' album.
                if ($this->sessionFunctions->is_logged_in() && $this->sessionFunctions->can_upload()) {
                    $query->orWhere('album_id', '=', null);

                    $id = $this->sessionFunctions->id();
                    if ($id !== 0) {
                        $query->where('owner_id', '=', $id);
                    }
                }
            }
        );

        $full_photo = Configs::get_value('full_photo', '1') === '1';
        $return['photos'] = $this->albumFunctions->photosLocationData($query, $full_photo);

        return $return;
    }
}
