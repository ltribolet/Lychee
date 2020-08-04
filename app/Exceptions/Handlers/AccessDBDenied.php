<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Throwable;

class AccessDBDenied
{
    /**
     * Render an exception into an HTTP response.
     */
    public function check(Request $request, Throwable $exception): bool
    {
        return $exception instanceof QueryException && \mb_strpos($exception->getMessage(), 'Access denied') > 0;
    }

    /**
     * @return Application|RedirectResponse|Response|Redirector
     */
    public function go()
    {
        return ToInstall::go();
    }
}
