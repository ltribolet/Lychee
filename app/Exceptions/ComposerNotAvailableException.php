<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class ComposerNotAvailableException extends Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('composer (software) is not available.', $code, $previous);
    }
}
