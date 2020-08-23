<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\Models\Configs;
use Illuminate\Console\Command;

class ResetAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lychee:reset_admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset Login and Password of the admin user.';

    /**
     * Add color to the command line output.
     *
     * @var Colorize
     */
    private $col;

    public function __construct(Colorize $colorize)
    {
        parent::__construct();

        $this->col = $colorize;
    }

    public function handle(): void
    {
        Configs::where('key', '=', 'username')->orWhere('key', '=', 'password')->update(['value' => '']);
        $this->line($this->col->yellow('Admin username and password reset.'));
    }
}
