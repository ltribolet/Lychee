<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class RequestAdminDataException extends Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Trying to get a User from Admin ID.', $code, $previous);
    }
}
