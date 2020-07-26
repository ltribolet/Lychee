<?php

declare(strict_types=1);

use App\Configs;
use Illuminate\Database\Migrations\Migration;

class BumpVersion040002 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configs::where('key', 'version')->update(['value' => '040002']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', 'version')->update(['value' => '040001']);
    }
}
