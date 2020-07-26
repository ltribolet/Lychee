<?php

declare(strict_types=1);

namespace App\SmartAlbums;

use App\Configs;
use App\Photo;
use Illuminate\Database\Eloquent\Builder;

class StarredAlbum extends SmartAlbum
{
    public function get_title(): string
    {
        return 'starred';
    }

    public function get_photos(): Builder
    {
        // php7.4: return Photo::stars()->where(fn ($q) => $this->filter($q));
        return Photo::stars()->where(function ($q) {
            return $this->filter($q);
        });
    }

    public function is_public(): bool
    {
        return Configs::get_value('public_starred', '0') === '1';
    }
}
