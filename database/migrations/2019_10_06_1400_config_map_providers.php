<?php

declare(strict_types=1);

use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigMapProviders extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('MAP_PROVIDERS') or define(
            'MAP_PROVIDERS',
            'Wikimedia|OpenStreetMap.org|OpenStreetMap.de|OpenStreetMap.fr|RRZE'
        );

        DB::table('configs')->insert([
            [
                'key' => 'map_provider',
                'value' => 'Wikimedia',
                'confidentiality' => 0,
                'cat' => 'Mod Map',
                'type_range' => MAP_PROVIDERS,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', '=', 'map_provider')->delete();
    }
}
