<?php

declare(strict_types=1);

namespace App\Models;

/**
 * App/Response.
 */
class Response
{
    /**
     * Return a json encoded string as a Warning.
     * The Warning: is used by the front end to recognise the kind of message.
     *
     * @param $msg
     *
     * @throws \JsonException
     */
    public static function warning(string $msg): string
    {
        return \json_encode('Warning: ' . $msg, JSON_THROW_ON_ERROR);
    }

    /**
     * Return a json encoded string as am Error.
     * The Error: is used by the front end to recognise the kind of message.
     *
     * @param $msg
     *
     * @throws \JsonException
     */
    public static function error(string $msg): string
    {
        return \json_encode('Error: ' . $msg, JSON_THROW_ON_ERROR);
    }

    public static function json(string $str, ?int $options = 0): string
    {
        return \json_encode($str, JSON_THROW_ON_ERROR | $options);
    }
}
