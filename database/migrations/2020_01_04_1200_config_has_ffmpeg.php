<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigHasFFmpeg extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('BOOL') or define('BOOL', '0|1');
        defined('TERNARY') or define('TERNARY', '0|1|2');

        // Let's run the check for ffmpeg right here
        // not set
        $has_ffmpeg = 2;
        try {
            $path = exec('command -v ffmpeg');
            if ($path === '') {
                // false
                $has_ffmpeg = 0;
            } else {
                // true
                $has_ffmpeg = 1;
            }
        } catch (\Throwable $e) {
            $has_ffmpeg = 0;
            // let's do nothing
        }

        DB::table('configs')->insert([
            [
                'key' => 'has_ffmpeg',
                'value' => $has_ffmpeg,
                'confidentiality' => 2,
                'cat' => 'Image Processing',
                'type_range' => TERNARY,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', '=', 'has_ffmpeg')->delete();
    }
}
