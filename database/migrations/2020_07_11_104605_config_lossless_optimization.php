<?php

declare(strict_types=1);

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigLosslessOptimization extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('BOOL') or define('BOOL', '0|1');
        DB::table('configs')->insert([
            [
                'key' => 'lossless_optimization',
                'value' => '1',
                'confidentiality' => '2',
                'cat' => 'Image Processing',
                'type_range' => BOOL,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', '=', 'lossless_optimization')->delete();
    }
}
