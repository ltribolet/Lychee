<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class Rss extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('BOOL') or define('BOOL', '0|1');
        defined('INT') or define('INT', 'int');

        DB::table('configs')->insert([
            [
                'key' => 'rss_enable',
                'value' => '0',
                'confidentiality' => '0',
                'cat' => 'Mod RSS',
                'type_range' => BOOL,
            ],
            [
                'key' => 'rss_recent_days',
                'value' => '7',
                'confidentiality' => '0',
                'cat' => 'Mod RSS',
                'type_range' => INT,
            ],
            [
                'key' => 'rss_max_items',
                'value' => '100',
                'confidentiality' => '0',
                'cat' => 'Mod RSS',
                'type_range' => INT,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', '=', 'rss_enable')->delete();
        Configs::where('key', '=', 'rss_recent_days')->delete();
        Configs::where('key', '=', 'rss_max_items')->delete();
    }
}
