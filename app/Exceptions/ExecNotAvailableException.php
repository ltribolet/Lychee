<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class ExecNotAvailableException extends Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('exec php function is not available.', $code, $previous);
    }
}
