<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Routing\Redirector;

class RedirectController extends Controller
{
    public function album(string $albumid): Redirector
    {
        return \redirect('gallery#' . $albumid);
    }

    public function photo(string $albumid, string $photoid): Redirector
    {
        return \redirect('gallery#' . $albumid . '/' . $photoid);
    }
}
