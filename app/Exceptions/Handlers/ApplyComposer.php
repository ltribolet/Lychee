<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class ApplyComposer
{
    /**
     * Render an exception into an HTTP response.
     */
    public function check(Request $request, Throwable $exception): bool
    {
        return $exception instanceof ErrorException && (\mb_strpos(
            $exception->getFile(),
            'laravel/framework/src/Illuminate/Routing/Router.php'
        ) !== false);
    }

    /**
     * @return Response or View
     */
    // @codeCoverageIgnoreStart
    public function go(): Response
    {
        return \response()->view(
            'error.error',
            [
                'code' => '500',
                'message' => 'Missing dependency, please do: <code>composer install --no-dev</code><br>(or use the release channel.)',
            ]
        );
    }
}
