<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigExiftool extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('BOOL') or define('BOOL', '0|1');

        DB::table('configs')->insert([
            [
                'key' => 'has_exiftool',
                'value' => null,
                'confidentiality' => 2,
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
        if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
            Configs::where('key', '=', 'has_exiftool')->delete();
        }
    }
}
