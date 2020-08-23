<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\ModelFunctions\SessionFunctions;
use Closure;
use Illuminate\Http\Request;

class LoginCheck
{
    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    public function __construct(SessionFunctions $sessionFunctions)
    {
        $this->sessionFunctions = $sessionFunctions;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->sessionFunctions->is_logged_in()) {
            return \response('false');
        }

        return $next($request);
    }
}
