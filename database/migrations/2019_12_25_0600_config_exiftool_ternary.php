<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigExiftoolTernary extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('BOOL') or define('BOOL', '0|1');
        defined('TERNARY') or define('TERNARY', '0|1|2');

        // Let's run the check for exiftool right here
        // not set
        $has_exiftool = 2;
        try {
            $path = exec('command -v exiftool');
            if ($path === '') {
                // false
                $has_exiftool = 0;
            } else {
                // true
                $has_exiftool = 1;
            }
        } catch (\Throwable $e) {
            // let's do nothing
        }

        Configs::where('key', '=', 'has_exiftool')
            ->update([
                'value' => $has_exiftool,
                'type_range' => TERNARY,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        defined('BOOL') or define('BOOL', '0|1');

        Configs::where('key', '=', 'has_exiftool')
            ->update([
                'value' => null,
                'type_range' => BOOL,
            ]);
    }
}
