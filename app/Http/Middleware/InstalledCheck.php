<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Redirections\ToHome;
use Illuminate\Http\Request;

class InstalledCheck
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        // base safety
        if (\file_exists(\base_path('installed.log'))) {
            return ToHome::go();
        }

        return $next($request);
    }
}
