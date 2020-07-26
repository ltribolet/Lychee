<?php

declare(strict_types=1);

namespace App\Locale;

class Lang
{
    /**
     * @return array<string>
     */
    public static function get_classes(): array
    {
        $return = [];
        $list_lang = \scandir(__DIR__);
        $iMax = \count($list_lang);
        for ($i = 0; $i < $iMax; $i++) {
            if ($list_lang[$i] !== '.' &&
                $list_lang[$i] !== '..' &&
                $list_lang[$i] !== 'Lang.php' &&
                $list_lang[$i] !== 'LangInterface.php'
            ) {
                $return[] = __NAMESPACE__ . '\\' . \mb_substr($list_lang[$i], 0, -4);
            }
        }

        return $return;
    }

    public static function get_lang(string $value = 'en'): string
    {
        $list_lang = self::get_classes();
        $count = \count($list_lang);
        for ($i = 0; $i < $count; $i++) {
            if ($list_lang[$i]::code() === $value) {
                return $list_lang[$i]::get_locale();
            }
        }

        // default: we force English
        /** @var LangInterface $class_name */
        $class_name = __NAMESPACE__ . '\\' . 'English';

        return $class_name::get_locale();
    }

    /**
     * @return array<string>
     */
    public static function get_lang_available(): array
    {
        $list_lang = self::get_classes();
        $return = [];
        $count = \count($list_lang);
        for ($i = 0; $i < $count; $i++) {
            $return[] = $list_lang[$i]::code();
        }

        return $return;
    }
}
