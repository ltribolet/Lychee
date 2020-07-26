<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Handlers\AccessDBDenied;
use App\Exceptions\Handlers\ApplyComposer;
use App\Exceptions\Handlers\InvalidPayload;
use App\Exceptions\Handlers\NoEncryptionKey;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<string>
     */
    protected array $dontReport = [DecryptException::class];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<string>
     */
    protected array $dontFlash = ['password', 'password_confirmation'];

    /**
     * Render an exception into an HTTP response.
     */
    public function render(Request $request, Throwable $exception): Response
    {
        $checks = [];
        $checks[] = new NoEncryptionKey();
        $checks[] = new InvalidPayload();
        $checks[] = new AccessDBDenied();
        $checks[] = new ApplyComposer();

        foreach ($checks as $check) {
            if ($check->check($request, $exception)) {
                // @codeCoverageIgnoreStart
                return $check->go();
                // @codeCoverageIgnoreEnd
            }
        }

        // @codeCoverageIgnoreStart
        return parent::render($request, $exception);
        // @codeCoverageIgnoreEnd
    }
}
