<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Response;
use Throwable;

class InvalidPayload
{
    /**
     * Render an exception into an HTTP response.
     */
    public function check(Illuminate\Http\Request $request, Throwable $exception): bool
    {
        return $exception instanceof DecryptException;
    }

    // @codeCoverageIgnoreStart
    public function go(): Response
    {
        return \response()->json(['error' => 'Session timed out'], 400);
    }

    // @codeCoverageIgnoreEnd
}
