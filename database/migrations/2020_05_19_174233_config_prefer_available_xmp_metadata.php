<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigPreferAvailableXmpMetadata extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('BOOL') or define('BOOL', '0|1');

        DB::table('configs')->insert([
            [
                'key' => 'prefer_available_xmp_metadata',
                'value' => '0',
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
        Configs::where('key', '=', 'prefer_available_xmp_metadata')->delete();
    }
}
