<?php

declare(strict_types=1);

namespace App\Services;

use App\Assets\Helpers;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

class DownloadAlbumService
{
    /**
     * @param array<string, array<string, mixed>> $albumsPhotos
     */
    public function handle(array $albumsPhotos): callable
    {
        return function () use ($albumsPhotos): void {
            $this->makeArchive($albumsPhotos);
        };
    }

    /**
     * This method starts outputting Headers, just so you know.
     *
     * @param array<string, array<string, mixed>> $albumsPhotos
     */
    public function makeArchive(array $albumsPhotos): void
    {
        if (\count($albumsPhotos) === 0) {
            return;
        }

        /* Extract this in archive factory */
        $archive = new Archive();
        $archive->setEnableZip64(Configs::get_value('zip64', '1') === '1');
        $zip = new ZipStream(null, $archive);

        $this->addFolders($albumsPhotos, $zip);

        $zip->finish();
    }

    /**
     * @param array<string, array<string, mixed>> $albumsPhotos
     */
    private function addFolders(array $albumsPhotos, ZipStream $zip, string $parentDir = ''): void
    {
        $folders = [];
        foreach ($albumsPhotos as $albumPhotos) {
            /** @var Album $album */
            $album = $albumPhotos['album'];

            if (!$album->isSmart() && !$album->public && (!Auth::check() || $album->owner_id !== Auth::user()->id)) {
                continue;
            }

            $photos = $albumPhotos['content']['photos'];
            $children = Arr::get($albumPhotos, 'content.children');

            $folder = $this->getFolder($album, $folders, $parentDir);
            $folders[] = $folder;

            $this->addPhotos($photos, $album, $zip, $folder, $children);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $albumsPhotos
     */
    public function getArchiveTitle(array $albumsPhotos): string
    {
        $zipTitle = 'Albums';
        if (\count($albumsPhotos) === 1) {
            $album = $albumsPhotos[0]['album'];
            $zipTitle = $album->getArchiveTitle();
        }

        return $zipTitle;
    }

    /**
     * @param array<string> $dirs
     */
    private function getFolder(Album $album, array $dirs, string $parentDir): string
    {
        $dir = $album->getArchiveTitle() ?? 'Untitled';
        $dir = $this->uniqueName($dir, \array_keys($dirs));
        if ($parentDir !== '') {
            $dir = $parentDir . '/' . $dir;
        }

        return $dir;
    }

    /**
     * @param array<string> $fileNames
     *
     * @return array<string>
     */
    private function getPhotoFile(Photo $photo, array $fileNames): array
    {
        $isRaw = $photo->type === 'raw';

        $prefix_url = $isRaw ? 'raw/' : 'big/';
        $filePath = Storage::path($prefix_url . $photo->url);
        // Check if readable
        if (!@\is_readable($filePath)) {
            Logs::error(__METHOD__, (string) __LINE__, 'Original photo missing: ' . $filePath);

            return ['', ''];
        }

        // Get extension of image
        $extension = Helpers::getExtension($filePath, false);

        // Set title for photo
        $title = $photo->getArchiveTitle();
        $title .= ($isRaw ? '' : $extension);
        $title = $this->uniqueName($title, $fileNames);

        return [$filePath, $title];
    }

    /**
     * @param array<string> $existingNames
     */
    private function uniqueName(string $givenName, array $existingNames): string
    {
        $i = 1;
        $tmpName = $givenName;
        $pos = \mb_strrpos($tmpName, '.') ?: \mb_strlen($tmpName);
        while (\in_array($tmpName, $existingNames, true)) {
            // Set new title for photo
            $tmpName = \substr_replace($givenName, '-' . $i, $pos, 0);
            ++$i;
        }

        return $tmpName;
    }

    /**
     * @param array<Photo> $photos
     * @param array<array<string, mixed>> $children
     */
    private function addPhotos(array $photos, Album $album, ZipStream $zip, string $folder, array $children): void
    {
        $files = [];
        /** @var Photo $photo */
        foreach ($photos as $photo) {
            if ($album->isSmart() && !Auth::check() && $photo->album_id && !$photo->album->is_downloadable()) {
                continue;
            }

            [$filePath, $title] = $this->getPhotoFile($photo, $files);

            $zip->addFileFromPath($folder . '/' . $title, $filePath);
        }

        if ($children) {
            $this->addFolders($children, $zip, $folder);
        }
    }
}
