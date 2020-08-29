<?php declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PhotoResource extends JsonResource
{
    /**
     * @var Photo
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $photoUrl = $this->getPhotoUrl();
        $photoUrl2x = $this->getPhotoUrl2x($photoUrl);
        $photoScales = $this->getPhotoScales($photoUrl, $photoUrl2x);
        $pathPrefix = $this->resource->type === 'raw' ? 'raw/' : 'big/';
        $license = $this->getLicense();

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'tags' => $this->resource->tags,
            'star' => (bool) $this->resource->star,
            'album' => $this->resource->album_id,
            'width' => $this->resource->width,
            'height' => $this->resource->height,
            'type' => $this->resource->type,
            'size' => $this->resource->size,
            'iso' => $this->resource->iso,
            'aperture' => $this->resource->aperture,
            'make' => $this->resource->make,
            'model' => $this->resource->model,
            'shutter' => $this->resource->shutter,
            'focal' => $this->resource->focal,
            'lens' => $this->resource->lens,
            'latitude' => $this->resource->latitude,
            'longitude' => $this->resource->longitude,
            'altitude' => $this->resource->altitude,
            'imgDirection' => $this->resource->imgDirection,
            'location' => $this->resource->location,
            'livePhotoContentID' => $this->resource->livePhotoContentID,
            'sysdate' => \optional($this->resource->created_at)->getTimestamp(),
            'takedate' => \optional($this->resource->takestamp)->getTimestamp(),
            'license' => $license,
            'medium' => $photoScales['medium'],
            'medium_dim' => $photoScales['medium_dim'],
            'medium2x' => $photoScales['medium2x'],
            'medium2x_dim' => $photoScales['medium2x_dim'],
            'small' => $photoScales['small'],
            'small_dim' => $photoScales['small_dim'],
            'small2x' => $photoScales['small2x'],
            'small2x_dim' => $photoScales['small2x_dim'],
            'thumbUrl' => Storage::url('thumb/' . $this->resource->thumbUrl),
            'thumb2x' => $photoScales['thumb2x'],
            'url' => Storage::url($pathPrefix . $this->resource->url),
            'livePhotoUrl' => $this->resource->livePhotoUrl ?
                Storage::url('big/' . $this->resource->livePhotoUrl) :
                null,
        ];
    }

    private function getPhotoUrl(): string
    {
        if (\mb_strpos($this->resource->type, 'video') === 0) {
            return $this->resource->thumbUrl;
        }

        if ($this->resource->type === 'raw') {
            // It's a raw file -> we also use jpeg as extension
            return $this->resource->thumbUrl;
        }

        return $this->resource->url;
    }

    private function getPhotoUrl2x(string $photoUrl): string
    {
        if ($photoUrl === '') {
            return '';
        }

        [$filename, $extension] = \explode('.', $photoUrl);

        return $filename . '@2x.' . $extension;
    }

    /**
     * @return array<string>
     */
    private function getPhotoScales(string $photoUrl, string $photoUrl2x): array
    {
        $scales = [
            'medium' => '',
            'medium_dim' => '',
            'medium2x' => '',
            'medium2x_dim' => '',
            'small' => '',
            'small_dim' => '',
            'small2x' => '',
            'small2x_dim' => '',
            'thumb2x' => '',
        ];

        if ($this->resource->medium !== '') {
            $scales['medium'] = Storage::url('medium/' . $photoUrl);
            $scales['medium_dim'] = $this->resource->medium;
        }

        if ($this->resource->medium2x !== '') {
            $scales['medium2x'] = Storage::url('medium/' . $photoUrl2x);
            $scales['medium2x_dim'] = $this->resource->medium2x;
        }

        if ($this->resource->small !== '') {
            $scales['small'] = Storage::url('small/' . $photoUrl);
            $scales['small_dim'] = $this->resource->small;
        }

        if ($this->resource->small2x !== '') {
            $scales['small2x'] = Storage::url('small/' . $photoUrl2x);
            $scales['small2x_dim'] = $this->resource->small2x;
        }

        if ($this->resource->thumb2x === '1') {
            [$thumbFilename, $thumbExtension] = \explode('.', $this->resource->thumbUrl);
            $thumbUrl2x = $thumbFilename . '@2x.' . $thumbExtension;
            $scales['thumb2x'] = Storage::url('thumb/' . $thumbUrl2x);
        }

        return $scales;
    }

    private function getLicense(): string
    {
        $license = $this->resource->license;
        if ($license !== 'none') {
            return $license;
        }

        if ($this->resource->album->license !== 'none') {
            return $this->resource->album->license;
        }

        return Configs::get_value('default_license');
    }
}
