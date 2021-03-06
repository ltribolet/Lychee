<?php

declare(strict_types=1);

namespace App\ModelFunctions;

use App\Assets\Helpers;
use App\Image\ImageHandlerInterface;
use App\Metadata\Extractor;
use App\ModelFunctions\AlbumActions\UpdateTakestamps as AlbumUpdate;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use App\Models\Response;
use FFMpeg;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use ImageOptimizer;

class PhotoFunctions
{
    /**
     * @var array
     */
    public $validTypes = [IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG];

    /**
     * @var array<string>
     */
    public array $validVideoTypes = [
        'video/mp4',
        'video/mpeg',
        'video/ogg',
        'video/webm',
        'video/quicktime',
        // wmv file
        'video/x-ms-asf',
        // Avi
        'video/x-msvideo',
        // Avi
        'video/x-m4v',
    ];

    /**
     * @var array<string>
     */
    public array $validExtensions = [
        '.jpg',
        '.jpeg',
        '.png',
        '.gif',
        '.ogv',
        '.mp4',
        '.mpg',
        '.webm',
        '.webp',
        '.mov',
        '.m4v',
        '.avi',
        '.wmv',
    ];

    /**
     * @var Extractor
     */
    private $metadataExtractor;

    /**
     * @var ImageHandlerInterface
     */
    private $imageHandler;

    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    /**
     * PhotoFunctions constructor.
     */
    public function __construct(
        Extractor $metadataExtractor,
        ImageHandlerInterface $imageHandler,
        SessionFunctions $sessionFunctions
    ) {
        $this->metadataExtractor = $metadataExtractor;
        $this->imageHandler = $imageHandler;
        $this->sessionFunctions = $sessionFunctions;
    }

    /**
     * Returns 'photo' if it is a photo
     * Returns 'video' if it is a video
     * Returns 'raw' if it is an accepted file (we only check extensions)
     * Returns 'error message' if it is something else.
     *
     * @param array<string> $file
     */
    public function file_type(array $file, string $extension): string
    {
        // check raw files
        $raw_formats = \mb_strtolower(Configs::get_value('raw_formats', ''));
        if (\in_array(\mb_strtolower($extension), \explode('|', $raw_formats), true)) {
            return 'raw';
        }

        if (! \in_array(\mb_strtolower($extension), $this->validExtensions, true)) {
            $mimeType = $file['type'];
            if (! \in_array($mimeType, $this->validVideoTypes, true)) {
                // let's check for the mimetype
                // maybe we don't have a photo
                if (! \function_exists('exif_imagetype')) {
                    Logs::error(
                        __METHOD__,
                        __LINE__,
                        'EXIF library not loaded. Make sure exif is enabled in php.ini'
                    );

                    return 'EXIF library not loaded on the server!';
                }

                $type = @\exif_imagetype($file['tmp_name']);
                if (! \in_array($type, $this->validTypes, true)) {
                    Logs::error(__METHOD__, (string) __LINE__, 'Photo type not supported: ' . $file['name']);

                    return 'Photo type not supported!';
                }
                // we have maybe a raw file
                Logs::error(__METHOD__, (string) __LINE__, 'Photo format not supported: ' . $file['name']);

                return 'Photo format not supported!';
            }
            // we have a video
            return 'video';
        }
        // we have a normal photo
        return 'photo';
    }

    /**
     * Add new photo(s) to the database.
     * Exits on error.
     *
     * @param array<string> $file
     * @param int|string $albumID_in
     *
     * @return string|false ID of the added photo
     */
    public function add(
        array $file,
        $albumID_in = 0,
        bool $delete_imported = false,
        bool $force_skip_duplicates = false,
        bool $resync_metadata = false
    ) {
        // Check permissions
        // TODO: extract this test.
        if (
            Helpers::hasPermissions(Storage::path('')) === false ||
            Helpers::hasPermissions(Storage::path('big/')) === false ||
            Helpers::hasPermissions(Storage::path('medium/')) === false ||
            Helpers::hasPermissions(Storage::path('small/')) === false ||
            Helpers::hasPermissions(Storage::path('thumb/')) === false ||
            Helpers::hasPermissions(Storage::path('import/')) === false
        ) {
            Logs::error(__METHOD__, (string) __LINE__, 'An upload-folder is missing or not readable and writable');

            return Response::error('An upload-folder is missing or not readable and writable!');
        }

        $public = 0;
        $star = 0;
        $albumID = null;

        switch ($albumID_in) {
            case 'public':
                $public = 1;
                break;

            case 'starred':
                $star = 1;
                break;

            case 'unsorted':
            // root
            case '0':
            case 'recent':
                break;
            default:
                $albumID = (int) $albumID_in;
                break;
        }

        // Verify extension
        $extension = Helpers::getExtension($file['name'], false);
        $mimeType = $file['type'];
        $kind = $this->file_type($file, $extension);

        if ($kind !== 'photo' && $kind !== 'video' && $kind !== 'raw') {
            return Response::error($kind);
        }

        // Generate id
        $photo = new Photo();
        $photo->id = Helpers::generateID();

        // Set paths
        $tmp_name = $file['tmp_name'];
        $photo_name = \md5(\microtime()) . $extension;

        $path_prefix = $kind !== 'raw' ? 'big/' : 'raw/';
        $path = Storage::path($path_prefix . $photo_name);

        // Calculate checksum
        $checksum = \sha1_file($tmp_name);
        if ($checksum === false) {
            // @codeCoverageIgnoreStart
            Logs::error(__METHOD__, (string) __LINE__, 'Could not calculate checksum for photo');

            return Response::error('Could not calculate checksum for photo!');
            // @codeCoverageIgnoreEnd
        }
        $photo->checksum = $checksum;
        $existing = $photo->isDuplicate($checksum);
        $exists = $existing !== false;

        if ($exists !== false) {
            $photo_name = $existing->url;
            $path = Storage::path($path_prefix . $existing->url);
            $photo->thumbUrl = $existing->thumbUrl;
            $photo->thumb2x = $existing->thumb2x;
            $photo->medium = $existing->medium;
            $photo->medium2x = $existing->medium2x;
            $photo->small = $existing->small;
            $photo->small2x = $existing->small2x;
            $photo->livePhotoUrl = $existing->livePhotoUrl;
            $photo->livePhotoChecksum = $existing->livePhotoChecksum;
            $photo->checksum = $existing->checksum;
            $photo->type = $existing->type;
            $mimeType = $photo->type;
        }

        if ($exists === false) {
            // Import if not uploaded via web
            if (! \is_uploaded_file($tmp_name)) {
                // TODO: use the storage facade here
                // Check if the user wants to create symlinks instead of copying the photo
                if (Configs::get_value('import_via_symlink', '0') === '1') {
                    if (! \symlink($tmp_name, $path)) {
                        // @codeCoverageIgnoreStart
                        Logs::error(__METHOD__, (string) __LINE__, 'Could not create symlink');

                        return Response::error('Could not create symlink!');
                        // @codeCoverageIgnoreEnd
                    }
                } elseif (! @\copy($tmp_name, $path)) {
                    // @codeCoverageIgnoreStart
                    Logs::error(__METHOD__, (string) __LINE__, 'Could not copy photo to uploads');

                    return Response::error('Could not copy photo to uploads!');
                // @codeCoverageIgnoreEnd
                } elseif ($delete_imported) {
                    @\unlink($tmp_name);
                }
            } elseif (! @\move_uploaded_file($tmp_name, $path)) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not move photo to uploads');

                return Response::error('Could not move photo to uploads!');
            }
        } else {
            // Photo already exists
            if ($delete_imported && ! \is_uploaded_file($tmp_name)) {
                @\unlink($tmp_name);
            }
            // Check if the user wants to skip duplicates
            if ($force_skip_duplicates || Configs::get_value('skip_duplicates', '0') === '1') {
                $metadataChanged = false;

                // Before we skip entirely, check if there is a sidecar file and if the metadata needs to be updated (from a sidecar)
                if ($resync_metadata === true) {
                    $info = $this->getFileMetadata($file, $path, $kind, $mimeType, $extension);
                    foreach ($info as $key => $value) {
                        if ($existing->{$key} !== null && $value !== $existing->{$key}) {
                            $metadataChanged = true;
                            $existing->{$key} = $value;
                        }
                    }
                }

                if ($metadataChanged === true) {
                    Logs::notice(__METHOD__, (string) __LINE__, 'Updating metdata of existing photo.');
                    $existing->save();

                    return Response::warning(
                        'This photo has been skipped because it\'s already in your library, but its metadata has been updated.'
                    );
                }

                Logs::notice(
                    __METHOD__,
                    (string) __LINE__,
                    'Skipped upload of existing photo because skipDuplicates is activated'
                );

                return Response::warning('This photo has been skipped because it\'s already in your library.');
            }
        }

        $info = $this->getFileMetadata($file, $path, $kind, $mimeType, $extension);

        // TODO: move this elsewhere
        $photo->title = $info['title'];
        $photo->url = $photo_name;
        $photo->description = $info['description'];
        $photo->tags = $info['tags'];
        $photo->width = $info['width'] ?: 0;
        $photo->height = $info['height'] ?: 0;
        $photo->type = ($info['type'] ?: $mimeType);
        $photo->size = $info['size'];
        $photo->iso = $info['iso'];
        $photo->aperture = $info['aperture'];
        $photo->make = $info['make'];
        $photo->model = $info['model'];
        $photo->lens = $info['lens'];
        $photo->shutter = $info['shutter'];
        $photo->focal = $info['focal'];
        $photo->takestamp = $info['takestamp'];
        $photo->latitude = $info['latitude'];
        $photo->longitude = $info['longitude'];
        $photo->altitude = $info['altitude'];
        $photo->imgDirection = $info['imgDirection'];
        $photo->location = $info['location'];
        $photo->livePhotoContentID = $info['livePhotoContentID'];
        $photo->public = $public;
        $photo->star = $star;

        $GoogleMicroVideoOffset = $info['MicroVideoOffset'];

        if ($albumID !== null) {
            $album = Album::find($albumID);
            if ($album === null) {
                $photo->album_id = null;
                $photo->owner_id = $this->sessionFunctions->id();
            } else {
                $photo->album_id = $albumID;
                $photo->owner_id = $album->owner_id;
            }
        } else {
            $photo->album_id = null;
            $photo->owner_id = $this->sessionFunctions->id();
        }

        $livePhotoPartner = false;
        if ($photo->livePhotoContentID) {
            $livePhotoPartner = $photo->findLivePhotoPartner($photo->livePhotoContentID, $photo->album_id);
        }

        $skip_db_entry_creation = false;
        // if both are a photo or a video -> it's not a live photo
        if (
            ! ($livePhotoPartner === false)
            && \in_array($photo->type, $this->validVideoTypes, true) ===
                \in_array($livePhotoPartner->type, $this->validVideoTypes, true)
        ) {
            $livePhotoPartner = false;
        }

        // I'm uploading a photo, video already exists
        if (! ($livePhotoPartner === false) && ! \in_array($photo->type, $this->validVideoTypes, true)) {
            $photo->livePhotoUrl = $livePhotoPartner->url;
            $photo->livePhotoChecksum = $livePhotoPartner->checksum;
            // Todo: Delete the livePhotoPartner
            $livePhotoPartner->predelete(true);
            $livePhotoPartner->delete();
        }

        if ($exists === false) {
            // Generate small files for 2 options:
            // (1) There is no Live Photo Partner
            // (2) There is a partner and we're uploading a photo
            if (($livePhotoPartner === false) || ! \in_array($photo->type, $this->validVideoTypes, true)) {
                // Set orientation based on EXIF data
                // but do not rotate if the image shall not be modified
                if (
                    $photo->type === 'image/jpeg' && isset($info['orientation']) && $info['orientation'] !== ''
                    && Configs::get_value('import_via_symlink', '0') === '0'
                ) {
                    $rotation = $this->imageHandler->autoRotate($path, $info);

                    if ($rotation !== [false, false]) {
                        $photo->width = $rotation['width'];
                        $photo->height = $rotation['height'];
                    }
                }

                // Set original date
                if ($info['takestamp'] !== '' && $info['takestamp'] !== 0 && $info['takestamp'] !== null) {
                    @\touch($path, \strtotime($info['takestamp']));
                }

                // For videos extract a frame from the middle
                $frame_tmp = '';
                if (\in_array($photo->type, $this->validVideoTypes, true)) {
                    try {
                        $frame_tmp = $this->extractVideoFrame($photo);
                    } catch (\Throwable $exception) {
                        Logs::error(__METHOD__, (string) __LINE__, $exception->getMessage());
                    }
                }

                if ($kind === 'raw') {
                    try {
                        $frame_tmp = $this->createJpgFromRaw($photo);
                    } catch (\Throwable $exception) {
                        Logs::error(__METHOD__, (string) __LINE__, $exception->getMessage());
                    }
                }

                // Create Thumb
                if ($kind === 'raw' && $frame_tmp === '') {
                    $photo->thumbUrl = '';
                    $photo->thumb2x = 0;
                } elseif (! \in_array($photo->type, $this->validVideoTypes, true) || $frame_tmp !== '') {
                    if (! $this->createThumb($photo, $frame_tmp)) {
                        Logs::error(__METHOD__, (string) __LINE__, 'Could not create thumbnail for photo');

                        return Response::error('Could not create thumbnail for photo!');
                    }

                    $photo->thumbUrl = \basename($photo_name, $extension) . '.jpeg';

                    $this->createSmallerImages($photo, $frame_tmp);

                    if ($GoogleMicroVideoOffset) {
                        $this->extractVideo($photo, $GoogleMicroVideoOffset, $frame_tmp);
                    }

                    if ($frame_tmp !== '') {
                        \unlink($frame_tmp);
                    }
                } else {
                    $photo->thumbUrl = '';
                    $photo->thumb2x = 0;
                }
            } else {
                // We're uploading a video -> overwrite everything from partner
                $livePhotoPartner->livePhotoUrl = $photo->url;
                $livePhotoPartner->livePhotoChecksum = $photo->checksum;
                $livePhotoPartner->save();
                $skip_db_entry_creation = true;
            }
        }
        // In case it's a live photo and we've uploaded the video
        if ($skip_db_entry_creation === true) {
            return $livePhotoPartner->id;
        }

        return $this->save($photo, $albumID);
    }

    /**
     * @param string $frame_tmp Path of the video frame
     */
    public function createSmallerImages(Photo $photo, string $frame_tmp = ''): void
    {
        if ($frame_tmp === '' || $photo->type === 'raw') {
            // Create medium file for normal photos and for raws
            $mediumMaxWidth = (int) Configs::get_value('medium_max_width');
            $mediumMaxHeight = (int) Configs::get_value('medium_max_height');
            $this->resizePhoto($photo, 'medium', $mediumMaxWidth, $mediumMaxHeight, $frame_tmp);

            if (Configs::get_value('medium_2x') === '1') {
                $this->resizePhoto($photo, 'medium2x', $mediumMaxWidth * 2, $mediumMaxHeight * 2, $frame_tmp);
            }
        }

        $smallMaxWidth = (int) Configs::get_value('small_max_width');
        $smallMaxHeight = (int) Configs::get_value('small_max_height');
        $this->resizePhoto($photo, 'small', $smallMaxWidth, $smallMaxHeight, $frame_tmp);

        if (Configs::get_value('small_2x') === '1') {
            $this->resizePhoto($photo, 'small2x', $smallMaxWidth * 2, $smallMaxHeight * 2, $frame_tmp);
        }
    }

    /**
     * @return string Path of the jpg file
     */
    public function createJpgFromRaw(Photo $photo): string
    {
        // we need imagick to do the job
        if (! Configs::hasImagick()) {
            Logs::notice(__METHOD__, (string) __LINE__, 'Saving JPG of raw file to failed: Imagick not installed.');

            return '';
        }

        $filename = $photo->url;
        $url = Storage::path('raw/' . $filename);
        $ext = \pathinfo($filename)['extension'];

        // test if Imagaick supports the filetype
        // Query return file extensions as all upper case
        if (! \in_array(\mb_strtoupper($ext), \Imagick::queryFormats(), true)) {
            Logs::notice(__METHOD__, (string) __LINE__, 'Filetype ' . $ext . ' not supported by Imagick.');

            return '';
        }

        $tmp_file = \tempnam(\sys_get_temp_dir(), 'lychee') . '.jpeg';
        Logs::notice(__METHOD__, (string) __LINE__, 'Saving JPG of raw file to ' . $tmp_file);

        $resWidth = $resHeight = 0;
        $width = $photo->width;
        $height = $photo->height;

        try {
            $this->imageHandler->scale($url, $tmp_file, $width, $height, $resWidth, $resHeight);
        } catch (\Throwable $e) {
            Logs::error(__METHOD__, (string) __LINE__, 'Failed to create JPG from raw file ' . $url . $filename);

            return '';
        }

        return $tmp_file;
    }

    /**
     * @return string Path of the video frame
     */
    public function extractVideoFrame(Photo $photo): string
    {
        if ($photo->aperture === '') {
            return '';
        }

        $ffmpeg = FFMpeg\FFMpeg::create();
        $video = $ffmpeg->open(Storage::path('big/' . $photo->url));
        $tmp = \tempnam(\sys_get_temp_dir(), 'lychee') . '.jpeg';
        Logs::notice(__METHOD__, (string) __LINE__, 'Saving frame to ' . $tmp);

        try {
            $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($photo->aperture / 2));
            $frame->save($tmp);
        } catch (\Throwable $e) {
            Logs::notice(__METHOD__, (string) __LINE__, 'Failed to extract snapshot from video ' . $tmp);
        }

        // check if the image has data
        $success = \file_exists($tmp) ? (\filesize($tmp) > 0) : false;

        if ($success) {
            // Optimize image
            if (Configs::get_value('lossless_optimization')) {
                ImageOptimizer::optimize($tmp);
            }
        } else {
            Logs::notice(__METHOD__, (string) __LINE__, 'Failed to extract snapshot from video ' . $tmp);
            try {
                $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0));
                $frame->save($tmp);
                $success = \file_exists($tmp) ? (\filesize($tmp) > 0) : false;
                if (! $success) {
                    Logs::notice(
                        __METHOD__,
                        (string) __LINE__,
                        'Fallback failed to extract snapshot from video ' . $tmp
                    );
                } else {
                    Logs::notice(
                        __METHOD__,
                        (string) __LINE__,
                        'Fallback successful - snapshot from video ' . $tmp . ' at t=0 created.'
                    );
                }
            } catch (\Throwable $e) {
                Logs::notice(__METHOD__, (string) __LINE__, 'Fallback failed to extract snapshot from video ' . $tmp);

                return '';
            }
        }

        return $tmp;
    }

    /**
     * Create thumbnail for a picture.
     *
     * @param string $frame_tmp Path of the video frame
     *
     * @return bool returns true when successful
     */
    public function createThumb(Photo $photo, string $frame_tmp = ''): bool
    {
        Logs::notice(__METHOD__, (string) __LINE__, 'Photo URL is ' . $photo->url);

        $src = $frame_tmp === '' ? Storage::path('big/' . $photo->url) : $frame_tmp;
        $photoName = \explode('.', $photo->url);
        $this->imageHandler->crop($src, Storage::path('thumb/' . $photoName[0] . '.jpeg'), 200, 200);

        if (
            Configs::get_value('thumb_2x') === '1' &&
            $photo->width >= 400 && $photo->height >= 400
        ) {
            // Retina thumbs
            $this->imageHandler->crop($src, Storage::path('thumb/' . $photoName[0] . '@2x.jpeg'), 400, 400);
            $photo->thumb2x = 1;
        } else {
            $photo->thumb2x = 0;
        }

        return true;
    }

    /**
     * Creates smaller copies of Photo.
     *
     * @param int $videoLengthBytes Path of the video frame
     */
    public function extractVideo(Photo $photo, int $videoLengthBytes, string $frame_tmp = ''): bool
    {
        // We extract the video from the jpg file
        // Google Motion Photo: See here for details
        if ($frame_tmp === '') {
            $filename = $photo->url;
        } else {
            $filename = $photo->thumbUrl;
        }

        $filename_video_mov = \basename($filename, Helpers::getExtension($filename, false)) . '.mov';

        $uploadFolder = Storage::path('big/');

        if (Helpers::hasPermissions($uploadFolder) === false) {
            Logs::notice(
                __METHOD__,
                (string) __LINE__,
                'Skipped extaction of video from live photo, because ' . $uploadFolder . ' is missing or not readable and writable.'
            );

            return false;
        }

        try {
            // 1. Extract the video part
            $fp = \fopen($uploadFolder . $photo->url, 'rb');
            // use a temporary file, will be delted once closed
            $fp_video = \tmpfile();

            // The MP4 file is located in the last bytes of the file
            // It needs to be negative
            \fseek($fp, -1 * $videoLengthBytes, SEEK_END);
            $data = \fread($fp, $videoLengthBytes);
            \fwrite($fp_video, $data, $videoLengthBytes);

            // 2. Convert file from mp4 to mov, but keeping audio and video codec
            // This is needed to LivePhotosKit which only accepts mov files
            // Computation is fast, since codecs, resolution, framerate etc. remain unchanged

            $ffmpeg = FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open(\stream_get_meta_data($fp_video)['uri']);
            $format = new MOVFormat();
            // Add additional parameter to extract the first video stream
            $format->setAdditionalParameters(['-map', '0:0']);
            $video->save($format, $uploadFolder . $filename_video_mov);

            // 3. Close files ($fp_video will be again deleted)
            \fclose($fp);
            \fclose($fp_video);

            // Save file path; Checksum calclation not needed since
            // we do not perform matching for Google Motion Photos (as for iOS Live Photos)
            $photo->livePhotoUrl = $filename_video_mov;
        } catch (\Throwable $exception) {
            Logs::error(__METHOD__, (string) __LINE__, $exception->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Creates smaller copies of Photo.
     *
     * @param string $frame_tmp Path of the video frame
     */
    public function resizePhoto(Photo $photo, string $type, int $maxWidth, int $maxHeight, string $frame_tmp = ''): bool
    {
        $width = $photo->width;
        $height = $photo->height;

        if ($frame_tmp === '') {
            $filename = $photo->url;
            $url = Storage::path('big/' . $filename);
        } else {
            $filename = $photo->thumbUrl;
            $url = $frame_tmp;
        }

        // Both image sizes of the same type are stored in the same folder
        // ie: medium and medium2x both belong in LYCHEE_UPLOADS_MEDIUM
        $pathType = \mb_strtoupper($type);
        if (($split = \mb_strpos($pathType, '2')) !== false) {
            $pathType = \mb_substr($pathType, 0, $split);
        }

        $uploadFolder = Storage::path(\mb_strtolower($pathType) . '/');
        if (Helpers::hasPermissions($uploadFolder) === false) {
            Logs::notice(
                __METHOD__,
                (string) __LINE__,
                'Skipped creation of medium-photo, because ' . $uploadFolder . ' is missing or not readable and writable.'
            );

            return false;
        }

        // Add the @2x postfix if we're dealing with an HiDPI type
        if (\mb_strpos($type, '2x') > 0) {
            $filename = \preg_replace('/^(.*)\.(.*)$/', '\1@2x.\2', $filename);
        }

        // Is photo big enough?
        if (($width <= $maxWidth || $maxWidth === 0) && ($height <= $maxHeight || $maxHeight === 0)) {
            Logs::notice(
                __METHOD__,
                (string) __LINE__,
                'No resize (image is too small: ' . $maxWidth . 'x' . $maxHeight . ')!'
            );

            return false;
        }

        $resWidth = $resHeight = 0;
        if (! $this->imageHandler->scale(
            $url,
            $uploadFolder . $filename,
            $maxWidth,
            $maxHeight,
            $resWidth,
            $resHeight
        )) {
            Logs::error(__METHOD__, (string) __LINE__, 'Failed to ' . $type . ' resize image');

            return false;
        }

        $photo->{$type} = $resWidth . 'x' . $resHeight;

        return true;
    }

    /**
     * This function aims to fix the duplicate entry key problem.
     *
     * TODO: find where the array to string conversion is...
     *
     * @param $albumID
     *
     * @return false|mixed|string
     */
    public function save(Photo $photo, ?int $albumID = null)
    {
        do {
            $retry = false;

            try {
                if (! $photo->save()) {
                    return Response::error('Could not save photo in database!');
                }
            } catch (QueryException $e) {
                $errorCode = $e->getCode();
                if ($errorCode === 23000 || $errorCode === 23505) {
                    // houston, we have a duplicate entry problem
                    do {
                        // Our ids are based on current system time, so
                        // wait randomly up to 1s before retrying.
                        \usleep(\random_int(0, 1000000));
                        $newId = Helpers::generateID();
                    } while ($newId === $photo->id);

                    $photo->id = $newId;
                    $retry = true;
                } else {
                    Logs::error(
                        __METHOD__,
                        __LINE__,
                        'Something went wrong, error ' . $errorCode . ', ' . $e->getMessage()
                    );

                    return Response::error('Something went wrong, error' . $errorCode . ', please check the logs');
                }
            }
        } while ($retry);

        // Just update the album while we are at it.
        if ($albumID !== null) {
            $album = Album::find($albumID);
            if ($album === null) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified album');

                return Response::error('Could not find specified album');
            }
            if (! AlbumUpdate::update_takestamps($album, [$photo->takestamp], true)) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not update album takestamps');

                return Response::error('Could not update album takestamps');
            }
        }

        // return the ID.
        return $photo->id;
    }

    /**
     * Validates whether $type is a valid image type.
     */
    public function isValidImageType(int $type): bool
    {
        return \in_array($type, $this->validTypes, true);
    }

    /**
     * Returns a list of valid image types.
     *
     * @return array<string>
     */
    public function getValidImageTypes(): array
    {
        return $this->validTypes;
    }

    /**
     * Validates whether $type is a valid video type.
     */
    public function isValidVideoType(string $type): bool
    {
        return \in_array($type, $this->validVideoTypes, true);
    }

    /**
     * Returns a list of valid video types.
     *
     * @return array<string>
     */
    public function getValidVideoTypes(): array
    {
        return $this->validVideoTypes;
    }

    /**
     * Validates whether $extension is a valid image or video extension.
     */
    public function isValidExtension(string $extension): bool
    {
        return \in_array(\mb_strtolower($extension), $this->validExtensions, true);
    }

    /**
     * Returns a list of valid image/video extensions.
     *
     * @return array<string>
     */
    public function getValidExtensions(): array
    {
        return $this->validExtensions;
    }

    /**
     * Central function for retrieving the metadata since this has to be called in more than one place.
     *
     * @param array<string> $file
     *
     * @return array<string>
     */
    private function getFileMetadata(
        array $file,
        string $path,
        string $kind,
        string $mimeType,
        string $extension
    ): array {
        $info = $this->metadataExtractor->extract($path, $mimeType);
        if ($kind === 'raw') {
            $info['type'] = 'raw';
        }

        // Use title of file if IPTC title missing
        if ($info['title'] === '') {
            if ($kind === 'raw') {
                $info['title'] = \mb_substr(\basename($file['name']), 0, 98);
            } elseif ($info['title'] === '') {
                $info['title'] = \mb_substr(\basename($file['name'], $extension), 0, 98);
            }
        }

        return $info;
    }
}
