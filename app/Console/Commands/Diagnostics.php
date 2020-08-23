<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\Http\Controllers\DiagnosticsController;
use App\Metadata\DiskUsage;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use Illuminate\Console\Command;

class Diagnostics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lychee:diagnostics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the diagnostics informations.';

    /**
     * @var ConfigFunctions
     */
    private $configFunctions;

    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    /**
     * @var DiskUsage
     */
    private $diskUsage;

    /**
     * Add color to the command line output.
     *
     * @var Colorize
     */
    private $col;

    /**
     * Create a new command instance.
     */
    public function __construct(
        ConfigFunctions $configFunctions,
        SessionFunctions $sessionFunctions,
        DiskUsage $diskUsage,
        Colorize $colorize
    ) {
        parent::__construct();

        $this->configFunctions = $configFunctions;
        $this->sessionFunctions = $sessionFunctions;
        $this->diskUsage = $diskUsage;
        $this->col = $colorize;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $ctrl = new DiagnosticsController($this->configFunctions, $this->sessionFunctions, $this->diskUsage);

        $this->line('');
        $this->line('');
        $this->block('Diagnostics', $ctrl->get_errors());
        $this->line('');
        $this->block('System Information', $ctrl->get_info());
        $this->line('');
        $this->block('Config Information', $ctrl->get_config());
    }

    /**
     * Format the block.
     *
     * @param array<string> $array
     */
    private function block(string $str, array $array): void
    {
        $this->line($this->col->cyan($str));
        $this->line($this->col->cyan(\str_pad('', \mb_strlen($str), '-')));

        foreach ($array as $elem) {
            $elem = \str_replace(
                ['Error: ', 'Warning: '],
                [$this->col->red('Error: '), $this->col->yellow('Warning: ')],
                $elem
            );
            $this->line($elem);
        }
    }
}
