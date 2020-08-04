<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class InvalidPayload
{
    /**
     * Render an exception into an HTTP response.
     */
    public function check(Request $request, Throwable $exception): bool
    {
        return $exception instanceof DecryptException;
    }

    public function go(): JsonResponse
    {
        return \response()->json(['error' => 'Session timed out'], 400);
    }
}
