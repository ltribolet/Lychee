<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\Models\Logs;
use Illuminate\Console\Command;

class ShowLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lychee:logs {action=show : show or clean} {n=100 : number of lines} {order=DESC : ASCending or DESCending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print the logs table.';

    /**
     * Add color to the command line output.
     *
     * @var Colorize
     */
    private $col;

    /**
     * Create a new command instance.
     */
    public function __construct(Colorize $colorize)
    {
        parent::__construct();

        $this->col = $colorize;
    }

    public function handle(): void
    {
        $action = $this->argument('action');
        $n = (int) $this->argument('n');
        $order = $this->argument('order');

        if ($action === 'clean') {
            Logs::truncate();
            $this->line($this->col->yellow('Log table has been emptied.'));

            return;
        }

        if ($action !== 'show') {
            $n = (int) $this->argument('action');
            $order = $this->argument('n');
        }
        // we are in the show part but in the case where 'show' has not be defined.
        // as a results arguments are shifted: n <- action, order <- n.
        $this->action_show($n, $order);
    }

    private function action_show(int $n, string $order): void
    {
        $order = $order === 'ASC' || $order === 'DESC' ? $order : 'DESC';

        if (Logs::count() === 0) {
            $this->line($this->col->green('Everything looks fine, Lychee has not reported any problems!'));
        } else {
            $logs = Logs::orderBy('id', $order)->limit($n)->get();
            foreach ($logs->reverse() as $log) {
                $this->line($this->col->magenta($log->created_at)
                    . ' -- '
                    . $this->color_type(\str_pad($log->type, 7))
                    . ' -- '
                    . $this->col->blue($log->function)
                    . ' -- '
                    . $this->col->green($log->line)
                    . ' -- ' . $log->text);
            }
        }
    }

    private function color_type(string $type): string
    {
        switch ($type) {
            case 'error  ':
                return $this->col->red($type);
            case 'warning':
                return $this->col->yellow($type);
            case 'notice ':
                return $this->col->cyan($type);
            default:
                return $type;
        }
    }
}
