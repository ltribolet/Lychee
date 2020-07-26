<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class DivideByZeroException extends Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('gcd: Modulo by zero error.', $code, $previous);
    }
}
