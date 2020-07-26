<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class NotMasterException extends Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Branch is not master, cannot compare.', $code, $previous);
    }
}
