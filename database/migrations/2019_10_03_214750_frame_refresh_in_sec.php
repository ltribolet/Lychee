<?php

declare(strict_types=1);

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class FrameRefreshInSec extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configs::where('key', 'Mod_Frame_refresh')
            ->update([
                'value' => Configs::get_value('Mod_Frame_refresh') / 1000,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', 'Mod_Frame_refresh')
            ->update([
                'value' => Configs::get_value('Mod_Frame_refresh') * 1000,
            ]);
    }
}
