<?php

declare(strict_types=1);

namespace App\Models;

use App\Assets\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Storage;

/**
 * App\Models\Photo.
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $url
 * @property string $tags
 * @property int $public
 * @property int $owner_id
 * @property string $type
 * @property int|null $width
 * @property int|null $height
 * @property string $size
 * @property string $iso
 * @property string $aperture
 * @property string $make
 * @property string $model
 * @property string $lens
 * @property string $shutter
 * @property string $focal
 * @property float|null $latitude
 * @property float|null $longitude
 * @property float|null $altitude
 * @property float|null imgDirection
 * @property string|null location
 * @property Carbon|null $takestamp
 * @property int $star
 * @property string $thumbUrl
 * @property string $livePhotoUrl
 * @property int|null $album_id
 * @property string $checksum
 * @property string $license
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $medium
 * @property string $medium2x
 * @property string $small
 * @property string $small2x
 * @property int $thumb2x
 * @property string $livePhotoContentID
 * @property string $livePhotoChecksum
 * @property Album|null $album
 * @property User $owner
 *
 * @method static Builder|Photo newModelQuery()
 * @method static Builder|Photo newQuery()
 * @method static Builder|Photo ownedBy($id)
 * @method static Builder|Photo public ()
 * @method static Builder|Photo query()
 * @method static Builder|Photo recent()
 * @method static Builder|Photo stars()
 * @method static Builder|Photo unsorted()
 * @method static Builder|Photo whereAlbumId($value)
 * @method static Builder|Photo whereAltitude($value)
 * @method static Builder|Photo whereAperture($value)
 * @method static Builder|Photo whereChecksum($value)
 * @method static Builder|Photo whereCreatedAt($value)
 * @method static Builder|Photo whereDescription($value)
 * @method static Builder|Photo whereFocal($value)
 * @method static Builder|Photo whereHeight($value)
 * @method static Builder|Photo whereId($value)
 * @method static Builder|Photo whereImgDirection($value)
 * @method static Builder|Photo whereLocation($value)
 * @method static Builder|Photo whereIso($value)
 * @method static Builder|Photo whereLatitude($value)
 * @method static Builder|Photo whereLens($value)
 * @method static Builder|Photo whereLicense($value)
 * @method static Builder|Photo wherelivePhotoChecksum($value)
 * @method static Builder|Photo wherelivePhotoContentID($value)
 * @method static Builder|Photo wherelivePhotoUrl($value)
 * @method static Builder|Photo whereLongitude($value)
 * @method static Builder|Photo whereMake($value)
 * @method static Builder|Photo whereMedium($value)
 * @method static Builder|Photo whereMedium2x($value)
 * @method static Builder|Photo whereModel($value)
 * @method static Builder|Photo whereOwnerId($value)
 * @method static Builder|Photo wherePublic($value)
 * @method static Builder|Photo whereShutter($value)
 * @method static Builder|Photo whereSize($value)
 * @method static Builder|Photo whereSmall($value)
 * @method static Builder|Photo whereSmall2x($value)
 * @method static Builder|Photo whereStar($value)
 * @method static Builder|Photo whereTags($value)
 * @method static Builder|Photo whereTakestamp($value)
 * @method static Builder|Photo whereThumb2x($value)
 * @method static Builder|Photo whereThumbUrl($value)
 * @method static Builder|Photo whereTitle($value)
 * @method static Builder|Photo whereType($value)
 * @method static Builder|Photo whereUpdatedAt($value)
 * @method static Builder|Photo whereUrl($value)
 * @method static Builder|Photo whereWidth($value)
 * @mixin \Eloquent
 *
 * @property string|null $imgDirection
 * @property string|null $location
 *
 * @method static Builder|Photo public()
 * @method static Builder|Photo whereLivePhotoChecksum($value)
 * @method static Builder|Photo whereLivePhotoContentID($value)
 * @method static Builder|Photo whereLivePhotoUrl($value)
 */
class Photo extends Model
{
    /**
     * This extends the date types from Model to allow coercion with Carbon object.
     *
     * @var array dates
     */
    protected $dates = ['created_at', 'updated_at', 'takestamp'];

    protected $casts = [
        'public' => 'int',
        'owner_id' => 'int',
        'album_id' => 'int',
        'star' => 'int',
        'downloadable' => 'int',
        'share_button_visible' => 'int',
    ];

    /**
     * Return the relationship between a Photo and its Album.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo('App\Models\Album', 'album_id', 'id')->withDefault(['public' => '1']);
    }

    /**
     * Return the relationship between a Photo and its Owner.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id')->withDefault([
            'id' => 0,
            'username' => 'Admin',
        ]);
    }

    /**
     * Searches for a match of the livePhotoContentID to build a pair
     * of photo and video to form a live Photo
     * Warning: Only return the first hit!
     *
     * @return Photo|bool|Builder|Model|object
     */
    public function findLivePhotoPartner(string $livePhotoContentID, ?string $albumID = null)
    {
        // Todo: We need to search for pairs (Video + Photo)
        // Photo+Photo or Video+Video does not work
        $sql = $this->where('livePhotoContentID', '=', $livePhotoContentID)
            ->where('album_id', '=', $albumID)
            ->whereNull('livePhotoUrl');

        return $sql->count() === 0 ? false : $sql->first();
    }

    /**
     * Check if a photo already exists in the database via its checksum.
     *
     * @return Photo|bool|Builder|Model|object
     */
    public function isDuplicate(string $checksum, ?int $photoID = null)
    {
        $sql = $this->where(function ($q) use ($checksum): void {
            $q->where('checksum', '=', $checksum)
                ->orWhere('livePhotoChecksum', '=', $checksum);
        });
        if (isset($photoID)) {
            $sql = $sql->where('id', '<>', $photoID);
        }

        return $sql->count() === 0 ? false : $sql->first();
    }

    /**
     * ! how is this different than Cast::to_array ?
     * Returns photo-attributes into a front-end friendly format. Note that some attributes remain unchanged.
     *
     * @return array<mixed> returns photo-attributes in a normalized structure
     */
    public function prepareLocationData(): array
    {
        // Init
        $photo = [];

        // Set unchanged attributes
        $photo['id'] = (string) $this->id;
        $photo['title'] = $this->title;
        $photo['album'] = $this->album_id !== null ? (string) $this->album_id : null;
        $photo['latitude'] = $this->latitude;
        $photo['longitude'] = $this->longitude;

        // if this is a video
        if (\mb_strpos($this->type, 'video') === 0) {
            $photoUrl = $this->thumbUrl;
        } else {
            $photoUrl = $this->url;
        }

        $photoUrl2x = '';
        if ($photoUrl !== '') {
            $photoUrl2x = \explode('.', $photoUrl);
            $photoUrl2x = $photoUrl2x[0] . '@2x.' . $photoUrl2x[1];
        }

        if ($this->small !== '') {
            $photo['small'] = Storage::url('small/' . $photoUrl);
        } else {
            $photo['small'] = '';
        }

        if ($this->small2x !== '') {
            $photo['small2x'] = Storage::url('small/' . $photoUrl2x);
        } else {
            $photo['small2x'] = '';
        }

        // Parse paths
        $photo['thumbUrl'] = Storage::url('thumb/' . $this->thumbUrl);

        if ($this->thumb2x === '1') {
            $thumbUrl2x = \explode('.', $this->thumbUrl);
            $thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
            $photo['thumb2x'] = Storage::url('thumb/' . $thumbUrl2x);
        } else {
            $photo['thumb2x'] = '';
        }

        $path_prefix = $this->type === 'raw' ? 'raw/' : 'big/';
        $photo['url'] = Storage::url($path_prefix . $this->url);

        if (isset($this->takestamp) && $this->takestamp !== null) {
            // Use takestamp
            $photo['takedate'] = $this->takestamp->format('d F Y \a\t H:i');
        } else {
            $photo['takedate'] = '';
        }

        return $photo;
    }

    /**
     * TODO: Move me
     * Downgrade the quality of the pictures.
     *
     * @param array<string> $return
     */
    public function downgrade(array &$return): void
    {
        if (\mb_strpos($this->type, 'video') === 0) {
            if ($return['medium2x'] !== '') {
                $return['url'] = '';
            } elseif ($return['medium'] !== '') {
                $return['url'] = '';
            }
        }
    }

    /**
     * Retun the shutter speed as a proper string.
     */
    public function get_shutter_str(): string
    {
        $shutter = $this->shutter;
        // shutter speed needs to be processed. It is stored as a string `a/b s`
        if ($shutter !== '' && \mb_strpos($shutter, '1/') !== 0) {
            \preg_match('/(\d+)\/(\d+) s/', $shutter, $matches);
            if ($matches) {
                $a = (int) $matches[1];
                $b = (int) $matches[2];
                if ($b !== 0) {
                    try {
                        $gcd = Helpers::gcd($a, $b);
                        $a /= $gcd;
                        $b /= $gcd;
                    } catch (\Throwable $e) {
                        // this should not happen as we covered the case $b = 0;
                    }
                    if ($a === 1) {
                        $shutter = '1/' . $b . ' s';
                    } else {
                        $shutter = ($a / $b) . ' s';
                    }
                }
            }
        }

        if ($shutter === '1/1 s') {
            $shutter = '1 s';
        }

        return $shutter;
    }

    /**
     * TODO: [get rid of/move] me.
     * Get the public value of a picture
     * if 0 : picture is private
     * if 1 : picture is public alone
     * if 2 : picture is public by album being public (if being in an album).
     */
    public function get_public(): string
    {
        $ret = $this->public === 1 ? '1' : '0';

        if ($this->album_id !== null) {
            $ret = $this->album->public === '1' ? '2' : $ret;
        }

        return $ret;
    }

    /**
     * Before calling the delete() method which will remove the entry from the database, we need to remove the files.
     *
     * @todo refactor
     */
    public function predelete(bool $keep_original = false): bool
    {
        if ($this->isDuplicate($this->checksum, $this->id)) {
            Logs::notice(__METHOD__, (string) __LINE__, $this->id . ' is a duplicate!');
            // it is a duplicate, we do not delete!
            return true;
        }

        $error = false;
        $path_prefix = $this->type === 'raw' ? 'raw/' : 'big/';
        if ($keep_original === false) {
            // quick check...
            if (! Storage::exists($path_prefix . $this->url)) {
                Logs::error(
                    __METHOD__,
                    (string) __LINE__,
                    'Could not find file in ' . Storage::path($path_prefix . $this->url)
                );
                $error = true;
            } elseif (! Storage::delete($path_prefix . $this->url)) {
                Logs::error(
                    __METHOD__,
                    __LINE__,
                    'Could not delete file in ' . Storage::path($path_prefix . $this->url)
                );
                $error = true;
            }
        }

        if ((\mb_strpos($this->type, 'video') === 0) || ($this->type === 'raw')) {
            $photoName = $this->thumbUrl;
        } else {
            $photoName = $this->url;
        }
        if ($photoName !== '') {
            $photoName2x = \explode('.', $photoName);
            $photoName2x = $photoName2x[0] . '@2x.' . $photoName2x[1];

            // Delete Live Photo Video file
            // TODO: USE STORAGE FOR DELETE
            // check first if livePhotoUrl is available
            if ($this->livePhotoUrl !== null) {
                if (! Storage::exists('big/' . $this->livePhotoUrl)) {
                    Logs::error(
                        __METHOD__,
                        __LINE__,
                        'Could not find file in ' . Storage::path('big/' . $this->livePhotoUrl)
                    );
                    $error = true;
                } elseif (! Storage::delete('big/' . $this->livePhotoUrl)) {
                    Logs::error(
                        __METHOD__,
                        __LINE__,
                        'Could not delete file in ' . Storage::path('big/' . $this->livePhotoUrl)
                    );
                    $error = true;
                }
            }

            // Delete medium
            // TODO: USE STORAGE FOR DELETE
            if (Storage::exists('medium/' . $photoName) && ! \unlink(Storage::path('medium/' . $photoName))) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not delete photo in uploads/medium/');
                $error = true;
            }

            // TODO: USE STORAGE FOR DELETE
            if (Storage::exists('medium/' . $photoName2x) && ! \unlink(Storage::path('medium/' . $photoName2x))) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not delete high-res photo in uploads/medium/');
                $error = true;
            }

            // Delete small
            // TODO: USE STORAGE FOR DELETE
            if (Storage::exists('small/' . $photoName) && ! \unlink(Storage::path('small/' . $photoName))) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not delete photo in uploads/small/');
                $error = true;
            }

            // TODO: USE STORAGE FOR DELETE
            if (Storage::exists('small/' . $photoName2x) && ! \unlink(Storage::path('small/' . $photoName2x))) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not delete high-res photo in uploads/small/');
                $error = true;
            }
        }

        if ($this->thumbUrl !== '') {
            // Get retina thumb url
            $thumbUrl2x = \explode('.', $this->thumbUrl);
            $thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
            // Delete thumb
            // TODO: USE STORAGE FOR DELETE
            if (Storage::exists('thumb/' . $this->thumbUrl) && ! \unlink(Storage::path('thumb/' . $this->thumbUrl))) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not delete photo in uploads/thumb/');
                $error = true;
            }

            // Delete thumb@2x
            // TODO: USE STORAGE FOR DELETE
            if (Storage::exists('thumb/' . $thumbUrl2x) && ! \unlink(Storage::path('thumb/' . $thumbUrl2x))) {
                Logs::error(__METHOD__, (string) __LINE__, 'Could not delete high-res photo in uploads/thumb/');
                $error = true;
            }
        }

        return ! $error;
    }

    /**
     *  Defines a bunch of helpers.
     */

    /**
     * @param $query
     *
     * @return mixed
     */
    public static function set_order(Builder $query)
    {
        $sortingCol = Configs::get_value('sorting_Photos_col');
        if ($sortingCol !== 'title' && $sortingCol !== 'description') {
            $query->orderBy($sortingCol, Configs::get_value('sorting_Photos_order'));
        }

        return $query->orderBy('photos.id', 'ASC');
    }

    /**
     * Define scopes which we can directly use e.g. Photo::stars()->all().
     */

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeStars(Builder $query)
    {
        return $query->where('star', '=', 1);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopePublic(Builder $query)
    {
        return $query->where('public', '=', 1);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeRecent(Builder $query)
    {
        return $query->where(
            'created_at',
            '>=',
            Carbon::now()->subDays((int) Configs::get_value('recent_age', '1'))->toDateTimeString()
        );
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeUnsorted(Builder $query)
    {
        return $query->where('album_id', '=', null);
    }

    /**
     * @param $query
     * @param $id
     *
     * @return mixed
     */
    public function scopeOwnedBy(Builder $query, int $id)
    {
        return $id === 0 ? $query : $query->where('owner_id', '=', $id);
    }

    public function getArchiveTitle(): string
    {
        return \str_replace(Config::get('file.invalid_characters'), '', $this->title) ?: 'Untitled';
    }
}
