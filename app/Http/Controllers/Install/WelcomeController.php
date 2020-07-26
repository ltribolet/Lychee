<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;

final class WelcomeController extends Controller
{
    public function view(): View
    {
        // Show separator
        return \view('install.welcome', [
            'title' => 'Lychee-installer',
            'step' => 0,
        ]);
    }
}
