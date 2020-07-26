<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class NoOnlineUpdateException extends Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Online updates are not allowed.', $code, $previous);
    }
}
