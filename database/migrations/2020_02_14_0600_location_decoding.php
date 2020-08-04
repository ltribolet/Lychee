<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocationDecoding extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('BOOL') or define('BOOL', '0|1');
        defined('INT') or define('INT', 'int');

        DB::table('configs')->insert([
            'key' => 'location_decoding',
            'value' => '0',
            'cat' => 'Mod Map',
            'type_range' => BOOL,
            'confidentiality' => '0',
        ]);
        DB::table('configs')->insert([
            'key' => 'location_decoding_timeout',
            'value' => 30,
            'cat' => 'Mod Map',
            'type_range' => INT,
            'confidentiality' => '0',
        ]);
        DB::table('configs')->insert([
            'key' => 'location_show',
            'value' => '1',
            'cat' => 'Mod Map',
            'type_range' => BOOL,
            'confidentiality' => '0',
        ]);
        DB::table('configs')->insert([
            'key' => 'location_show_public',
            'value' => '0',
            'cat' => 'Mod Map',
            'type_range' => BOOL,
            'confidentiality' => '0',
        ]);

        Schema::table('photos', function ($table): void {
            $table->string('location')->default(null)->after('imgDirection')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', '=', 'location_decoding')->delete();
        Configs::where('key', '=', 'location_decoding_timeout')->delete();
        Configs::where('key', '=', 'location_show')->delete();
        Configs::where('key', '=', 'location_show_public')->delete();
        Schema::table('photos', function (Blueprint $table): void {
            $table->dropColumn('location');
        });
    }
}
