<?php

declare(strict_types=1);

namespace App\Redirections;

use Illuminate\Http\RedirectResponse;

class ToHome implements Redirection
{
    public static function go(): RedirectResponse
    {
        // we directly redirect to gallery
        return \redirect(\route('home'), 307, [
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }
}
