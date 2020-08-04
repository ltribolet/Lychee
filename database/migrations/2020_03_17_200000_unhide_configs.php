<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class UnhideConfigs extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configs::where('key', 'SL_enable')->update(['confidentiality' => '2']);
        Configs::where('key', 'SL_for_admin')->update(['confidentiality' => '2']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', 'SL_enable')->update(['confidentiality' => '0']);
        Configs::where('key', 'SL_for_admin')->update(['confidentiality' => '0']);
    }
}
