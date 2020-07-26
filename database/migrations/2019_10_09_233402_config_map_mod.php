<?php

declare(strict_types=1);

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigMapMod extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configs::where('key', '=', 'map_display')->update(['cat' => 'Mod Map']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', '=', 'map_display')->update(['cat' => 'config']);
    }
}
