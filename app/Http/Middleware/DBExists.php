<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Redirections\ToInstall;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DBExists
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Schema::hasTable('configs')) {
            return ToInstall::go();
        }

        return $next($request);
    }
}
