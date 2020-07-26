<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Throwable;

class AlbumDoesNotExistsException extends Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Album does not exist.', $code, $previous);
    }

    public function render(Request $request): string
    {
        return 'false';
    }
}
