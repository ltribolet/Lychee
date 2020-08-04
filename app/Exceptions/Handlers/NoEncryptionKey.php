<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use App\Redirections\ToInstall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Throwable;

class NoEncryptionKey
{
    /**
     * Render an exception into an HTTP response.
     */
    public function check(Request $request, Throwable $exception): bool
    {
        // encryption key does not exist, we need to run the installation
        return $exception instanceof RuntimeException
            && $exception->getMessage() === 'No application encryption key has been specified.';
    }

    /**
     * @return RedirectResponse|Response
     */
    public function go()
    {
        return ToInstall::go();
    }
}
