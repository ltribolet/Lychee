<?php

declare(strict_types=1);

namespace App\ControllerFunctions;

use App\Exceptions\AlbumDoesNotExistsException;
use App\ModelFunctions\SessionFunctions;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;

class ReadAccessFunctions
{
    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    public function __construct(SessionFunctions $sessionFunctions)
    {
        $this->sessionFunctions = $sessionFunctions;
    }

    /**
     * Check if a (public) user has access to an album
     * if 0 : album does not exist
     * if 1 : access is granted
     * if 2 : album is private
     * if 3 : album is password protected and require user input.
     */
    public function album(Album $album, bool $obeyHidden = false): int
    {
        if ($this->sessionFunctions->is_current_user($album->owner_id)) {
            // access granted
            return 1;
        }

        // Check if the album is shared with us
        if (
            $this->sessionFunctions->is_logged_in() &&
            $album->shared_with->map(function ($user) {
                return $user->id;
            })->contains($this->sessionFunctions->id())
        ) {
            // access granted
            return 1;
        }

        if (
            $album->public !== 1 ||
            ($obeyHidden && $album->visible_hidden !== 1)
        ) {
            // Warning: Album private!
            return 2;
        }

        if (empty($album->password)) {
            // access granted
            return 1;
        }

        if ($this->sessionFunctions->has_visible_album($album->id)) {
            // access granted
            return 1;
        }

        // Please enter password first. // Warning: Wrong password!
        return 3;
    }

    /**
     * Check if a (public) user has access to an album
     * if 0 : album does not exist
     * if 1 : access is granted
     * if 2 : album is private
     * if 3 : album is password protected and require user input.
     *
     * @param int|string $album: Album object or Album id
     */
    public function albumID($album, bool $obeyHidden = false): int
    {
        if (\in_array($album, ['starred', 'public', 'recent', 'unsorted'], true)) {
            if ($this->sessionFunctions->is_logged_in() && $this->sessionFunctions->can_upload()) {
                return 1;
            }
            if (($album === 'recent' && Configs::get_value('public_recent', '0') === '1') ||
                ($album === 'starred' && Configs::get_value('public_starred', '0') === '1')
            ) {
                // access granted
                return 1;
            }
            // Warning: Album private!
            return 2;
        }

        $albumModel = Album::find($album);
        if ($albumModel === null) {
            throw new AlbumDoesNotExistsException();
        }

        return $this->album($albumModel, $obeyHidden);
    }

    /**
     * Check if a (public) user has access to a picture.
     */
    public function photo(Photo $photo): bool
    {
        if ($this->sessionFunctions->is_current_user($photo->owner_id)) {
            return true;
        }
        if ($photo->public === 1) {
            return true;
        }
        if ($this->albumID($photo->album_id) === 1) {
            return true;
        }

        return false;
    }
}
