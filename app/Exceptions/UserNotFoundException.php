<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class UserNotFoundException extends Exception
{
    public function __construct(int $id = 0, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Could not find specified user (' . $id . ')', $code, $previous);
    }
}
