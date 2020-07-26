<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\ControllerFunctions\Install\ApplyMigration;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MigrationController extends Controller
{
    /**
     * @var ApplyMigration
     */
    protected $applyMigration;

    public function __construct(ApplyMigration $applyMigration)
    {
        $this->applyMigration = $applyMigration;
    }

    public function view(): View
    {
        $output = [];

        $error = $this->applyMigration->migrate($output);
        $output[] = '';
        if (!$error) {
            $error = $this->applyMigration->keyGenerate($output);
        }
        $output[] = '';
        if (!$error) {
            $this->installed($output);
        }
        $error = $error ? true : null;

        return \view('install.migrate', [
            'title' => 'Lychee-installer',
            'step' => 4,
            'lines' => $output,
            'errors' => $error,
        ]);
    }

    /**
     * @param array<string> $output
     */
    public function installed(array &$output): void
    {
        $dateStamp = \date('Y-m-d H:i:s');
        $message = 'Lychee INSTALLED on ' . $dateStamp;
        \file_put_contents(\base_path('installed.log'), $message);
        $output[] = $message;
        $output[] = 'Created installed.log';
    }
}
