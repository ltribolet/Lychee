<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class GitNotExecutableException extends Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            \base_path('.git') . ' (and subdirectories) are not executable, check the permissions.', $code, $previous
        );
    }
}
