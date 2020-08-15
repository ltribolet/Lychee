<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Album;
use App\Services\AlbumFactory;
use Illuminate\Contracts\Validation\Rule;

class AlbumExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     */
    public function passes($attribute, $value): bool
    {
        if (\array_key_exists($value, AlbumFactory::$albumMap)) {
            return true;
        }

        return \optional(Album::find($value))->count() > 0;
    }

    public function message(): string
    {
        return 'The album :attribute does not exist.';
    }
}
