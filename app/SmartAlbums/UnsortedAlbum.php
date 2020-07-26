<?php

declare(strict_types=1);

namespace App\SmartAlbums;

use App\Photo;
use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends SmartAlbum
{
    public $id;

    public function get_title(): string
    {
        return 'unsorted';
    }

    public function get_photos(): Builder
    {
        // php7.4: return Photo::unsorted()->where(fn ($q) => $this->filter($q));
        return Photo::unsorted()->where(function ($q) {
            return $this->filter($q);
        });
    }

    public function is_public(): bool
    {
        return false;
    }
}
