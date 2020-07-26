<?php

declare(strict_types=1);

namespace App\SmartAlbums;

use App\Photo;
use Illuminate\Database\Eloquent\Builder;

class PublicAlbum extends SmartAlbum
{
    public function get_title(): string
    {
        return 'public';
    }

    public function get_photos(): Builder
    {
        // php7.4: return Photo::public()->where(fn ($q) => $this->filter($q));
        return Photo::public()->where(function ($q) {
            return $this->filter($q);
        });
    }
}
