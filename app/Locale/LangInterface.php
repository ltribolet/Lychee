<?php

declare(strict_types=1);

namespace App\Locale;

interface LangInterface
{
    public static function code(): string;

    /**
     * @return array<string>
     */
    public static function get_locale(): array;
}
