<?php

declare(strict_types=1);

namespace App\Redirections;

use Illuminate\Http\RedirectResponse;

interface Redirection
{
    public static function go(): RedirectResponse;
}
