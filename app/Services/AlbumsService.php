<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Album;
use App\Models\Configs;
use App\Models\SmartAlbums\PublicAlbum;
use App\Models\SmartAlbums\RecentAlbum;
use App\Models\SmartAlbums\SmartAlbum;
use App\Models\SmartAlbums\StarredAlbum;
use App\Models\SmartAlbums\UnsortedAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlbumsService
{
    public function getVisibleAlbums(?int $parent = null): Collection
    {
        $databaseAlbums = $this->getDatabaseAlbums($parent);

        $this->getSmartAlbums()->each(function (SmartAlbum $smartAlbum) use ($databaseAlbums): void {
            $databaseAlbums->push($smartAlbum);
        });

        return $databaseAlbums;
    }

    public function getDatabaseAlbums(?int $parent): Collection
    {
        $sortingCol = Configs::get_value('sorting_Albums_col');
        $sortingOrder = Configs::get_value('sorting_Albums_order');

        $albums = Album::with(['children', 'owner'])->where('parent_id', $parent);

        if (!Auth::check()) {
            return $this->getGuestVisibleAlbums($albums, $sortingCol, $sortingOrder);
        }

        return $this->getUserVisibleAlbums($albums, $sortingCol, $sortingOrder);
    }

    private function getGuestVisibleAlbums(Builder $albums, ?string $sortingCol, ?string $sortingOrder): Collection
    {
        $albums
            ->where('public', true)
            ->where('visible_hidden', true);

        return $this->sortAlbums($albums, $sortingCol, $sortingOrder);
    }

    private function getUserVisibleAlbums(Builder $albums, ?string $sortingCol, ?string $sortingOrder): Collection
    {
        if (!Auth::user()->isAdmin()) {
            $user = Auth::user();
            $albums->where(function ($query) use ($user): void {
                $query->where('owner_id', '=', $user->id);
                $query->orWhereIn('id', DB::table('user_album')->select('album_id')->where('user_id', '=', $user->id));
                $query->orWhere(function ($_query): void {
                    $_query
                        ->where('public', true)
                        ->where('visible_hidden', true);
                });
            });
        }

        $albums->orderBy('owner_id', 'ASC');

        return $this->sortAlbums($albums, $sortingCol, $sortingOrder);
    }

    private function sortAlbums(Builder $albums, ?string $sortingCol, ?string $sortingOrder): Collection
    {
        if (!\in_array($sortingCol, ['title', 'description'], true)) {
            return $albums->orderBy($sortingCol, $sortingOrder)->get();
        }

        return $albums
            ->get()
            ->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'DESC');
    }

    private function getSmartAlbums(): Collection
    {
        $smartAlbums = new Collection();

        if (!Auth::check() || !(Auth::user()->isAdmin() || Auth::user()->upload)) {
            return $smartAlbums;
        }

        $smartAlbums->push(new UnsortedAlbum());
        $smartAlbums->push(new StarredAlbum());
        $smartAlbums->push(new PublicAlbum());
        $smartAlbums->push(new RecentAlbum());

        return $smartAlbums;
    }
}
