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
        foreach ($list_lang as $i => $iValue) {
            if ($iValue !== '.' &&
                $iValue !== '..' &&
                $iValue !== 'Lang.php' &&
                $iValue !== 'LangInterface.php'
            ) {
                $return[] = __NAMESPACE__ . '\\' . \mb_substr($list_lang[$i], 0, -4);
            }
        }

        return $return;
    }

    /**
     * @return array<string>
     */
    public static function get_lang(string $value = 'en'): array
    {
        $list_lang = self::get_classes();
        foreach ($list_lang as $iValue) {
            if ($iValue::code() === $value) {
                return $iValue::get_locale();
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
        foreach ($list_lang as $iValue) {
            $return[] = $iValue::code();
        }

        return $return;
    }
}
