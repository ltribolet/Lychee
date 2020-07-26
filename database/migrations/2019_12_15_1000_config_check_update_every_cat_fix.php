<?php

declare(strict_types=1);

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigCheckUpdateEveryCatFix extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configs::where('key', 'update_check_every_days')->update(['cat' => 'config']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', 'update_check_every_days')->update(['cat' => 'Config']);
    }
}
