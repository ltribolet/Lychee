<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class AddForce32BitIds extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('BOOL') or define('BOOL', '0|1');

        DB::table('configs')->insert([
            [
                'key' => 'force_32bit_ids',
                'value' => '0',
                'cat' => 'config',
                'type_range' => BOOL,
                'confidentiality' => '0',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', '=', 'force_32bit_ids')->delete();
    }
}
