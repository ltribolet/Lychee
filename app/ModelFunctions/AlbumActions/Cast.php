<?php

declare(strict_types=1);

namespace App\ModelFunctions\AlbumActions;

use App\Assets\Helpers;
use App\ModelFunctions\PhotoActions\Cast as PhotoCast;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Collection as BaseCollection;

class Cast
{
    /**
     * Returns album-attributes into a front-end friendly format. Note that some attributes remain unchanged.
     *
     * @return array<mixed>
     */
    public static function toArray(Album $album): array
    {
        return [
            'id' => (string) $album->id,
            'title' => $album->title,
            'public' => (string) $album->public,
            'full_photo' => Helpers::str_of_bool($album->is_full_photo_visible()),
            'visible' => (string) $album->visible_hidden,
            'parent_id' => $album->str_parent_id(),
            'description' => (string) $album->description,

            'downloadable' => Helpers::str_of_bool($album->is_downloadable()),
            'share_button_visible' => Helpers::str_of_bool($album->is_share_button_visible()),

            // Parse date
            'sysdate' => $album->created_at->format('F Y'),
            'min_takestamp' => $album->str_min_takestamp(),
            'max_takestamp' => $album->str_max_takestamp(),

            // Parse password
            'password' => Helpers::str_of_bool(!empty($album->password)),
            'license' => $album->get_license(),

            'thumbs' => [],
            'thumbs2x' => [],
            'types' => [],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function toArrayWith(Album $album, BaseCollection $children): array
    {
        $album_array = self::toArray($album);

        // php7.4: $album_array['albums'] = $children[$key]->map(fn ($e) => AlbumCast::toArrayWith($e[0], $e[1]));
        $album_array['albums'] = $children->map(function ($e) {
            return self::toArrayWith($e[0], $e[1]);
        })->values();
        // we need values because we need to reset the keys for when logged in.

        return $album_array;
    }

    /**
     * Set up the wrap arround of the photos if setting is true and if there are enough pictures.
     *
     * @param array<mixed> $return_photos
     */
    public static function wrapAroundPhotos(array &$return_photos): void
    {
        $photo_counter = \count($return_photos);

        if ($photo_counter > 1 && Configs::get_value('photos_wraparound', '1') === '1') {
            // Enable next and previous for the first and last photo
            $lastElement = \end($return_photos);
            $lastElementId = $lastElement['id'];
            $firstElement = \reset($return_photos);
            $firstElementId = $firstElement['id'];

            $return_photos[$photo_counter - 1]['nextPhoto'] = $firstElementId;
            $return_photos[0]['previousPhoto'] = $lastElementId;
        }
    }

    /**
     * Given an Album, return the thumbs of its 3 first pictures (excluding subalbums).
     *
     * @param array<mixed> $return
     */
    public static function getThumbs(array &$return, Album $album, SymLinkFunctions $symLinkFunctions): void
    {
        $photos = $album->getPhotos()->get();
        $return['thumbs'] = [];
        $return['thumbs2x'] = [];
        $return['types'] = [];
        $return['num'] = (string) $photos->count();

        $k = 0;
        foreach ($photos as $photo) {
            if ($k < 3) {
                $ret = PhotoCast::toThumb($photo, $symLinkFunctions);
                $ret->insertToArrays($return['thumbs'], $return['types'], $return['thumbs2x']);
                ++$k;
            } else {
                break;
            }
        }
    }
}
