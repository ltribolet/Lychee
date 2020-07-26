<?php

declare(strict_types=1);

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class BumpVersion040006 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configs::where('key', 'version')->update(['value' => '040006']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', 'version')->update(['value' => '040005']);
    }
}
