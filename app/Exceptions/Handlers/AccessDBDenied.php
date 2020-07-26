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
        // encryption key does not exist, we need to run the installation
        return $exception instanceof QueryException && (\mb_strpos(
            $exception->getMessage(),
            'Access denied'
        ) !== false);
    }

    /**
     * @return Application|RedirectResponse|Response|Redirector
     */
    // @codeCoverageIgnoreStart
    public function go()
    {
        return ToInstall::go();
    }

    // @codeCoverageIgnoreEnd
}
