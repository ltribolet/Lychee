<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class RedirectController extends Controller
{
    public function album(string $albumid): RedirectResponse
    {
        return \redirect('gallery#' . $albumid);
    }

    public function photo(string $albumid, string $photoid): RedirectResponse
    {
        return \redirect('gallery#' . $albumid . '/' . $photoid);
    }
}
