<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Diagnostics;

interface DiagnosticCheckInterface
{
    /**
     * @param array<string> $errors
     */
    public function check(array &$errors): void;
}
