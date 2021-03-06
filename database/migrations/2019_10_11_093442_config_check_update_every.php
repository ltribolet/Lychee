<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigCheckUpdateEvery extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('INT') or define('INT', 'int');

        DB::table('configs')->insert([
            [
                'key' => 'update_check_every_days',
                'value' => '3',
                'confidentiality' => 2,
                'cat' => 'Config',
                'type_range' => INT,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', '=', 'update_check_every_days')->delete();
    }
}
