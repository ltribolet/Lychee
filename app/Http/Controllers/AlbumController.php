<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Assets\Helpers;
use App\ControllerFunctions\ReadAccessFunctions;
use App\ModelFunctions\AlbumActions\Cast as AlbumCast;
use App\ModelFunctions\AlbumActions\UpdateTakestamps as AlbumUpdate;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\AlbumsFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use App\Models\Response;
use App\Models\User;
use App\Services\AlbumFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class AlbumController extends Controller
{
    /**
     * @var AlbumFactory
     */
    private AlbumFactory $albumFactory;
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

    /**
     * @var readAccessFunctions
     */
    private $readAccessFunctions;

    public function __construct(
        AlbumFactory $albumFactory,
        AlbumFunctions $albumFunctions,
        AlbumsFunctions $albumsFunctions,
        SessionFunctions $sessionFunctions,
        ReadAccessFunctions $readAccessFunctions
    ) {
        $this->albumFunctions = $albumFunctions;
        $this->albumsFunctions = $albumsFunctions;
        $this->sessionFunctions = $sessionFunctions;
        $this->readAccessFunctions = $readAccessFunctions;
        $this->albumFactory = $albumFactory;
    }

    /**
     * Add a new Album.
     *
     * @return false|string
     */
    public function add(Request $request)
    {
        $request->validate([
            'title' => 'string|required|max:100',
            'parent_id' => 'int|nullable',
        ]);

        $album = $this->albumFunctions->create(
            $request['title'],
            (int) $request['parent_id'],
            $this->sessionFunctions->id()
        );

        return Response::json((string) $album->id, JSON_NUMERIC_CHECK);
    }

    /**
     * Provided an albumID, returns the album.
     *
     * @deprecated
     *
     * @return array<mixed>
     */
    public function get(Request $request): array
    {
        $request->validate(['albumID' => 'string|required']);
        $return['albums'] = [];
        // Get photos
        $album = $this->albumFactory->getAlbum($request['albumID']);

        if ($album->isSmart()) {
            $return = AlbumCast::toArray($album);
            $publicAlbums = $this->albumsFunctions->getPublicAlbumsId();
            $album->setAlbumIDs($publicAlbums);
        } else {
            // take care of sub albums
            $children = $this->albumFunctions->get_children($album, 0, true);

            $return = AlbumCast::toArrayWith($album, $children);
            $return['owner'] = $album->owner->username;

            $thumbs = $this->albumFunctions->get_thumbs($album, $children);
            $this->albumFunctions->set_thumbs_children($return['albums'], $thumbs[1]);
        }

        // take care of photos
        $full_photo = (bool) ($return['full_photo'] ?? Configs::get_value('full_photo', '1') === '1');
        $photos_query = $album->get_photos();
        $return['photos'] = $this->albumFunctions->photos($photos_query, $full_photo, $album->get_license());

        $return['id'] = $request['albumID'];
        $return['num'] = (string) \count($return['photos']);

        // finalize the loop
        if ($return['num'] === '0') {
            $return['photos'] = false;
        }

        return $return;
    }

    /**
     * Provided an albumID, returns the album.
     *
     * @param int|string $albumId
     *
     * @return array<mixed>
     */
    public function show($albumId): array
    {
        $return['albums'] = [];
        // Get photos
        $album = $this->albumFactory->getAlbum($albumId);

        if ($album->isSmart()) {
            $return = AlbumCast::toArray($album);
            $publicAlbums = $this->albumsFunctions->getPublicAlbumsId();
            $album->setAlbumIDs($publicAlbums);
        } else {
            // take care of sub albums
            $children = $this->albumFunctions->get_children($album, 0, true);

            $return = AlbumCast::toArrayWith($album, $children);
            $return['owner'] = $album->owner->username;

            $thumbs = $this->albumFunctions->get_thumbs($album, $children);
            $this->albumFunctions->set_thumbs_children($return['albums'], $thumbs[1]);
        }

        // take care of photos
        $full_photo = (bool) ($return['full_photo'] ?? Configs::get_value('full_photo', '1') === '1');
        $photos_query = $album->get_photos();
        $return['photos'] = $this->albumFunctions->photos($photos_query, $full_photo, $album->get_license());

        $return['id'] = $albumId;
        $return['num'] = (string) \count($return['photos']);

        // finalize the loop
        if ($return['num'] === '0') {
            $return['photos'] = false;
        }

        return $return;
    }

    /**
     * Provided an albumID, returns the album with only map related data.
     *
     * @deprecated
     *
     * @return array<mixed>
     */
    public function getPositionData(Request $request): array
    {
        $request->validate(['albumID' => 'string|required']);
        $request->validate(['includeSubAlbums' => 'string|required']);
        $return = [];
        // Get photos
        $album = $this->albumFactory->getAlbum($request['albumID']);

        if ($album->isSmart()) {
            $publicAlbums = $this->albumsFunctions->getPublicAlbumsId();
            $album->setAlbumIDs($publicAlbums);
            $photos_sql = $album->get_photos();
        } else {
            // take care of sub albums
            $album_list = \collect();
            if ($request['includeSubAlbums']) {
                // Get all subalbums of the current album
                $album_list = $album_list->concat($this->albumFunctions->get_sub_albums($album));
            }

            // Add current albumID to array
            $album_list->push($request['albumID']);
            $photos_sql = Photo::whereIn('album_id', $album_list);
        }

        $full_photo = $album->is_full_photo_visible();

        $return['photos'] = $this->albumFunctions->photosLocationData($photos_sql, $full_photo);
        $return['id'] = $request['albumID'];

        return $return;
    }

    /**
     * Provided an albumID, returns the album with only map related data.
     *
     * @param string|int $albumId
     *
     * @return array<mixed>
     */
    public function showPositionData($albumId, Request $request): array
    {
        $request->validate(['includeSubAlbums' => 'string|required']);
        $return = [];
        // Get photos
        $album = $this->albumFactory->getAlbum($albumId);

        if ($album->isSmart()) {
            $publicAlbums = $this->albumsFunctions->getPublicAlbumsId();
            $album->setAlbumIDs($publicAlbums);
            $photos_sql = $album->get_photos();
        } else {
            // take care of sub albums
            $album_list = \collect();
            if ($request['includeSubAlbums']) {
                // Get all subalbums of the current album
                $album_list = $album_list->concat($this->albumFunctions->get_sub_albums($album));
            }

            // Add current albumID to array
            $album_list->push($albumId);
            $photos_sql = Photo::whereIn('album_id', $album_list);
        }

        $full_photo = $album->is_full_photo_visible();

        $return['photos'] = $this->albumFunctions->photosLocationData($photos_sql, $full_photo);
        $return['id'] = $albumId;

        return $return;
    }

    /**
     * Provided the albumID and passwords, return whether the album can be accessed or not.
     */
    public function getPublic(Request $request): string
    {
        $request->validate([
            'albumID' => 'string|required',
            'password' => 'string|nullable',
        ]);

        $album = $this->albumFactory->getAlbum($request['albumID']);

        if (!$album || $album->isSmart() || !$album->public) {
            return 'false';
        }

        if ($album->password === '' || $this->sessionFunctions->has_visible_album($album->id)) {
            return 'true';
        }

        if ($album->password === '' || Hash::check($request['password'], $album->password)) {
            $this->sessionFunctions->add_visible_album($album->id);

            return 'true';
        }

        return 'false';
    }

    /**
     * Provided a title and an albumID, change the title of the album.
     */
    public function setTitle(Request $request): string
    {
        $request->validate([
            'albumIDs' => 'string|required',
            'title' => 'string|required|max:100',
        ]);

        $albums = Album::whereIn('id', \explode(',', $request['albumIDs']))->get();

        $no_error = true;
        $albums->each(function ($album) use (&$no_error, $request): void {
            $album->title = $request['title'];
            $no_error &= $album->save();
        });

        return $no_error ? 'true' : 'false';
    }

    /**
     * Change the sharing properties of the album.
     *
     * @return bool|string
     */
    public function setPublic(Request $request)
    {
        $request->validate([
            'albumID' => 'integer|required',
            'public' => 'integer|required',
            'visible' => 'integer|required',
            'downloadable' => 'integer|required',
            'share_button_visible' => 'integer|required',
            'full_photo' => 'integer|required',
        ]);

        $album = Album::find($request['albumID']);

        /* @todo refactor into FormRequest */
        if ($album === null) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified album');

            return 'false';
        }

        // Convert values
        $album->full_photo = ($request['full_photo'] === '1' ? 1 : 0);
        $album->public = ($request['public'] === '1' ? 1 : 0);
        $album->visible_hidden = ($request['visible'] === '1' ? 1 : 0);
        $album->downloadable = ($request['downloadable'] === '1' ? 1 : 0);
        $album->share_button_visible = ($request['share_button_visible'] === '1' ? 1 : 0);

        // Set public
        if (!$album->save()) {
            return 'false';
        }

        // Reset permissions for photos
        if ($album->public === 1 && $album->photos()->count() > 0 && !$album->photos()->update(['public' => '0'])) {
            return 'false';
        }

        if ($request->has('password')) {
            $password = $request->get('password', '');
            if ($password !== '') {
                $album->password = \bcrypt($request['password']);
            } else {
                $album->password = null;
            }
            if (!$album->save()) {
                return 'false';
            }
        }

        return 'true';
    }

    /**
     * Change the description of the album.
     *
     * @return bool|string
     */
    public function setDescription(Request $request)
    {
        $request->validate([
            'albumID' => 'integer|required',
            'description' => 'string|nullable|max:1000',
        ]);

        $album = Album::find($request['albumID']);

        /* @todo replace with FormRequest */
        if ($album === null) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified album');

            return 'false';
        }

        $album->description = $request['description'] ?? '';

        return $album->save() ? 'true' : 'false';
    }

    /**
     * Set the license of the Album.
     *
     * @todo refactor into a service
     */
    public function setLicense(Request $request): string
    {
        $request->validate([
            'albumID' => 'required|string',
            'license' => 'required|string',
        ]);

        /** @var Album|null */
        $album = Album::find($request['albumID']);

        /* @todo replace with FormRequest */
        if ($album === null) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified album');

            return 'false';
        }

        $licenses = Helpers::get_all_licenses();

        /** @todo replace with an array search function */
        $found = false;
        $i = 0;
        while (!$found && $i < \count($licenses)) {
            if ($licenses[$i] === $request['license']) {
                $found = true;
            }
            ++$i;
        }
        if (!$found) {
            Logs::error(__METHOD__, (string) __LINE__, 'License not recognised: ' . $request['license']);

            return Response::error('License not recognised!');
        }

        $album->license = $request['license'];

        return $album->save() ? 'true' : 'false';
    }

    /**
     * Delete the album and all pictures in the album.
     *
     * @todo refactor into a service
     */
    public function delete(Request $request): string
    {
        $request->validate([
            'albumIDs' => 'string|required',
        ]);

        $no_error = true;
        if ($request['albumIDs'] === '0') {
            $photos = Photo::select_unsorted(Photo::ownedBy($this->sessionFunctions->id()))->get();
            foreach ($photos as $photo) {
                $no_error &= $photo->predelete();
                $no_error &= $photo->delete();
            }

            return $no_error ? 'true' : 'false';
        }

        /** @var array<Album> $albums */
        $albums = Album::whereIn('id', \explode(',', $request['albumIDs']))->get();

        foreach ($albums as $album) {
            $no_error &= $album->predelete();

            /** @var Album */
            $parentAlbum = null;
            if ($album->parent_id !== null) {
                $parentAlbum = $album->parent;
                $minTS = $album->min_takestamp;
                $maxTS = $album->max_takestamp;
            }

            $no_error &= $album->delete();

            if ($parentAlbum !== null) {
                $no_error &= AlbumUpdate::update_takestamps($parentAlbum, [$minTS, $maxTS], false);
            }
        }

        return $no_error ? 'true' : 'false';
    }

    /**
     * Merge albums. The first of the list is the destination of the merge.
     *
     * @todo refactor into a service
     */
    public function merge(Request $request): string
    {
        $request->validate([
            'albumIDs' => 'string|required',
        ]);

        // Convert to array
        $albumIDs = \explode(',', $request['albumIDs']);
        // Get first albumID
        $albumID = \array_shift($albumIDs);

        $album = Album::find($albumID);

        /* @todo replace with FormRequest */
        if ($album === null) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified albums');

            return 'false';
        }

        $photos = Photo::whereIn('album_id', $albumIDs)->get();
        $no_error = true;
        foreach ($photos as $photo) {
            $photo->album_id = $albumID;

            // just to be sure to handle ownership changes in the process.
            $photo->owner_id = $album->owner_id;

            $no_error &= $photo->save();
        }

        $albums = Album::whereIn('parent_id', $albumIDs)->get();
        $no_error = true;
        foreach ($albums as $album_t) {
            $album_t->parent_id = $albumID;

            // just to be sure to handle ownership changes in the process.
            $album_t->owner_id = $album->owner_id;
            $no_error &= $this->albumFunctions->setContentsOwner($album_t->id, $album->owner_id);

            $no_error &= $album_t->save();
        }
        $no_error &= $album->save();

        $albums = Album::whereIn('id', $albumIDs)->get();
        $takestamps = [];
        foreach ($albums as $album_t) {
            $parentAlbum = null;
            if ($album_t->parent_id !== null) {
                $parentAlbum = $album_t->parent;
                if ($parentAlbum === null) {
                    Logs::error(__METHOD__, (string) __LINE__, 'Could not find a parent album');
                    $no_error = false;
                }
            }

            \array_push($takestamps, $album_t->min_takestamp, $album_t->max_takestamp);

            $no_error &= $album_t->delete();

            if ($parentAlbum !== null) {
                $no_error &= AlbumUpdate::update_takestamps($parentAlbum, \array_slice($takestamps, -2), false);
            }
        }
        $no_error &= AlbumUpdate::update_takestamps($album, $takestamps, true);

        return $no_error ? 'true' : 'false';
    }

    /**
     * Move multiple albums into another album.
     *
     * @todo refactor into a service and maybe Merge and Move are alike
     */
    public function move(Request $request): string
    {
        $request->validate(['albumIDs' => 'string|required']);

        // Convert to array
        $albumIDs = \explode(',', $request['albumIDs']);

        // Get first albumID
        $albumID = \array_shift($albumIDs);

        $album_master = null;
        if ($albumID !== 0) {
            $album_master = Album::find($albumID);
            if ($album_master === null) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified albums');

                return 'false';
            }
        }

        $albums = Album::whereIn('id', $albumIDs)->get();
        $no_error = true;
        $takestamps = [];
        foreach ($albums as $album) {
            $oldParentID = $album->parent_id;

            if ($albumID !== 0) {
                $album->parent_id = $albumID;

                // just to be sure to handle ownership changes in the process.
                $album->owner_id = $album_master->owner_id;
                $no_error &= $this->albumFunctions->setContentsOwner([$album->id], $album_master->owner_id);
            } else {
                $album->parent_id = null;
            }

            $no_error &= $album->save();

            if ($album_master !== null) {
                \array_push($takestamps, $album->min_takestamp, $album->max_takestamp);
            }

            if ($oldParentID !== null) {
                $oldParentAlbum = Album::find($oldParentID);
                if ($oldParentAlbum === null) {
                    Logs::error(__METHOD__, (string) __LINE__, 'Could not find a parent album');

                    $no_error = false;
                }
                $no_error &= AlbumUpdate::update_takestamps(
                    $oldParentAlbum,
                    [$album->min_takestamp, $album->max_takestamp],
                    false
                );
            }
        }
        if ($album_master !== null) {
            $no_error &= AlbumUpdate::update_takestamps($album_master, $takestamps, true);
        }

        return $no_error ? 'true' : 'false';
    }

    /**
     * Return the archive of the pictures of the album and its subalbums.
     *
     * @todo refactor into a service
     *
     * @return string|StreamedResponse
     */
    public function getArchive(Request $request)
    {
        if (Storage::getDefaultDriver() === 's3') {
            Logs::error(__METHOD__, (string) __LINE__, 'getArchive not implemented for S3');

            return 'false';
        }

        // Illicit chars
        $badChars = \array_merge(\array_map('chr', \range(0, 31)), ['<', '>', ':', '"', '/', '\\', '|', '?', '*']);

        $request->validate([
            'albumIDs' => 'required|string',
        ]);

        $albumIDs = \explode(',', $request['albumIDs']);

        $zipTitle = 'Albums';
        if (\count($albumIDs) === 1) {
            $album = $this->albumFactory->getAlbum($albumIDs[0]);

            if ($album === null) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified album');

                return 'false';
            }

            $zipTitle = $album->getArchiveTitle();
        }

        $response = new StreamedResponse(function () use ($albumIDs, $badChars): void {
            $options = new \ZipStream\Option\Archive();
            $options->setEnableZip64(Configs::get_value('zip64', '1') === '1');
            $zip = new ZipStream(null, $options);

            $UserId = $this->sessionFunctions->id();

            $dirs = [];
            foreach ($albumIDs as $albumID) {
                $album = null;
                switch ($albumID) {
                    case 'starred':
                        $dir = 'Starred';
                        if ($this->sessionFunctions->is_logged_in()) {
                            $user = User::find($UserId);

                            if ($UserId === 0 || $user->upload) {
                                $photos_sql = Photo::select_stars(Photo::OwnedBy($UserId));
                                break;
                            }
                        }
                        $photos_sql = Photo::select_stars(
                            Photo::whereIn('album_id', $this->albumsFunctions->getPublicAlbumsId())
                        );
                        break;
                    case 'public':
                        $dir = 'Public';
                        $photos_sql = Photo::select_public(Photo::OwnedBy($UserId));
                        break;
                    case 'recent':
                        $dir = 'Recent';
                        if ($this->sessionFunctions->is_logged_in()) {
                            $user = User::find($UserId);

                            if ($UserId === 0 || $user->upload) {
                                $photos_sql = Photo::select_recent(Photo::OwnedBy($UserId));
                                break;
                            }
                        }
                        $photos_sql = Photo::select_recent(
                            Photo::whereIn('album_id', $this->albumsFunctions->getPublicAlbumsId())
                        );
                        break;
                    case 'unsorted':
                        $dir = 'Unsorted';
                        $photos_sql = Photo::select_unsorted(Photo::OwnedBy($UserId));
                        break;
                    default:
                        $album = Album::find($albumID);
                        if ($album === null) {
                            Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified album');

                            return;
                        }
                        $dir = $album->title;
                        $photos_sql = Photo::set_order(Photo::where('album_id', '=', $albumID));
                        break;
                }
                // switch (albumID)

                $compress_album = function ($photos_sql, $dir, &$dirs, $parent_dir, $album) use (
                    &$zip,
                    $badChars,
                    &$compress_album
                ): void {
                    if ($album !== null) {
                        if (!$this->sessionFunctions->is_current_user($album->owner_id) && !$album->is_downloadable()) {
                            return;
                        }
                    } elseif (
                        !$this->sessionFunctions->is_logged_in()
                        && Configs::get_value('downloadable', '0') === '0'
                    ) {
                        return;
                    }

                    $dir = \str_replace($badChars, '', $dir);
                    if ($dir === '') {
                        $dir = 'Untitled';
                    }
                    // Check for duplicates
                    if (!empty($dirs)) {
                        $i = 1;
                        $tmp_dir = $dir;
                        while (\in_array($tmp_dir, $dirs, true)) {
                            // Set new directory name
                            $tmp_dir = $dir . '-' . $i;
                            ++$i;
                        }
                        $dir = $tmp_dir;
                    }
                    $dirs[] = $dir;

                    if ($parent_dir !== '') {
                        $dir = $parent_dir . '/' . $dir;
                    }

                    $files = [];
                    $photos = $photos_sql->get();
                    // We don't bother with additional sorting here; who
                    // cares in what order photos are zipped?

                    foreach ($photos as $photo) {
                        // For photos in public smart albums, skip the ones
                        // that are not downloadable based on their actual
                        // parent album.
                        if (
                            $album === null && !$this->sessionFunctions->is_logged_in() &&
                            $photo->album_id !== null && !$photo->album->is_downloadable()
                        ) {
                            continue;
                        }

                        $is_raw = ($photo->type === 'raw');

                        $prefix_url = $is_raw ? 'raw/' : 'big/';
                        $url = Storage::path($prefix_url . $photo->url);
                        // Check if readable
                        if (!@\is_readable($url)) {
                            Logs::error(__METHOD__, (string) __LINE__, 'Original photo missing: ' . $url);
                            continue;
                        }

                        // Get extension of image
                        $extension = Helpers::getExtension($url, false);

                        // Set title for photo
                        $title = \str_replace($badChars, '', $photo->title);
                        if (!isset($title) || $title === '') {
                            $title = 'Untitled';
                        }

                        $file = $title . ($is_raw ? '' : $extension);

                        // Check for duplicates
                        if (!empty($files)) {
                            $i = 1;
                            $tmp_file = $file;
                            $pos = \mb_strrpos($tmp_file, '.');
                            while (\in_array($tmp_file, $files, true)) {
                                // Set new title for photo
                                $tmp_file = \substr_replace($file, '-' . $i, $pos, 0);
                                ++$i;
                            }
                            $file = $tmp_file;
                        }
                        // Add to array
                        $files[] = $file;

                        // Reset the execution timeout for every iteration.
                        \set_time_limit(\ini_get('max_execution_time'));

                        // add a file named 'some_image.jpg' from a local file 'path/to/image.jpg'
                        $zip->addFileFromPath($dir . '/' . $file, $url);
                    }
                    // foreach ($photos)

                    // Recursively compress subalbums
                    if ($album !== null) {
                        $subDirs = [];
                        foreach ($album->children as $subAlbum) {
                            if ($this->readAccessFunctions->album($subAlbum, true) === 1) {
                                $subSql = Photo::set_order(Photo::where('album_id', '=', $subAlbum->id));
                                $compress_album($subSql, $subAlbum->title, $subDirs, $dir, $subAlbum);
                            }
                        }
                    }
                };
                // $compress_album

                $compress_album($photos_sql, $dir, $dirs, '', $album);
            }
            // foreach ($albumIDs)

            // finish the zip stream
            $zip->finish();
        });

        // Set file type and destination
        $response->headers->set('Content-Type', 'application/x-zip');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $zipTitle . '.zip',
            \mb_check_encoding($zipTitle, 'ASCII') ? '' : 'Album.zip'
        );
        $response->headers->set('Content-Disposition', $disposition);

        // Disable caching
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
