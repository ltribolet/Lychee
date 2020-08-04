<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class BumpVersion extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configs::where('key', 'version')->update(['value' => '040001']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', 'version')->update(['value' => '040000']);
    }
}
