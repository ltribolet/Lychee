<?php

declare(strict_types=1);

use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigMapDisplayPublic extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('BOOL') or define('BOOL', '0|1');

        DB::table('configs')->insert([
            [
                'key' => 'map_display_public',
                'value' => '0',
                'confidentiality' => 0,
                'cat' => 'Mod Map',
                'type_range' => BOOL,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', '=', 'map_display_public')->delete();
    }
}
