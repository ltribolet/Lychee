<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\ModelFunctions\PhotoActions\Thumb;
use App\Models\Album as AlbumModel;
use App\Models\Configs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
{
    /**
     * @var AlbumModel
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<mixed>
     */
    public function toArray($request): array
    {
        $thumbs = $this->getPhotosThumbs();

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'public' => (bool) $this->resource->public,
            'full_photo' => $this->resource->is_full_photo_visible(),
            'visible' => (bool) $this->resource->visible_hidden,
            'parent_id' => $this->resource->parent_id,
            'description' => $this->resource->description,

            'downloadable' => $this->resource->is_downloadable(),
            'share_button_visible' => $this->resource->is_share_button_visible(),

            // Parse date
            'sysdate' => $this->resource->created_at->getTimestamp(),
            'min_takestamp' => \optional($this->resource->min_takestamp)->getTimestamp(),
            'max_takestamp' => \optional($this->resource->max_takestamp)->getTimestamp(),

            'password' => ! empty($this->resource->password),
            'license' => $this->resource->get_license(),

            'thumbs' => $thumbs['thumbs'],
            'thumbs2x' => $thumbs['thumbs2x'],
            'types' => $thumbs['types'],
            'photos' => PhotoResource::collection($this->getPhotos($this->resource->getPhotos())),
            'children' => $this->resource->relationLoaded('children') ?
                $this->resource->children()->get('id')->pluck('id')->toArray() :
                [],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    private function getPhotosThumbs(): array
    {
        $thumbs = [
            'thumbs' => [],
            'thumbs2x' => [],
            'types' => [],
        ];

        return $this->resource->getThumbs()->reduce(static function (array $thumbs, Thumb $thumb) {
            $thumbs['thumbs'][] = $thumb->thumb;
            $thumbs['thumbs2x'][] = $thumb->thumb2x;
            $thumbs['types'][] = $thumb->type;

            return $thumbs;
        }, $thumbs);
    }

    private function getPhotos(Builder $photos): Collection
    {
        $sortingCol = Configs::get_value('sorting_Photos_col');
        $sortingOrder = Configs::get_value('sorting_Photos_order');

        if (! \in_array($sortingCol, ['title', 'description'], true)) {
            return $photos->orderBy($sortingCol, $sortingOrder)->get();
        }

        return $photos
            ->get()
            ->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'DESC');
    }
}
