<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Diagnostics;

use App\Metadata\LycheeVersion;

class LycheeDBVersionCheck implements DiagnosticCheckInterface
{
    /**
     * @var LycheeVersion
     */
    private $lycheeVersion;

    /**
     * @var array<string>
     */
    private $versions;

    /**
     * @param array<string> $versions caching the return of lycheeVersion->get()
     */
    public function __construct(LycheeVersion $lycheeVersion, array $versions)
    {
        $this->lycheeVersion = $lycheeVersion;

        $this->versions = $versions;
    }

    /**
     * @param array<string> $errors
     */
    public function check(array &$errors): void
    {
        if ($this->lycheeVersion->isRelease && $this->versions['DB']['version'] < $this->versions['Lychee']['version']) {
            $errors[] = 'Error: Database is behind file versions. Please apply the migration.';
        }
    }
}
