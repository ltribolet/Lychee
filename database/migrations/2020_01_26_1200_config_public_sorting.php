<?php

declare(strict_types=1);

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigPublicSorting extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configs::where('key', 'sorting_Albums_col')->update(['confidentiality' => '0']);
        Configs::where('key', 'sorting_Albums_order')->update(['confidentiality' => '0']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configs::where('key', 'sorting_Albums_col')->update(['confidentiality' => '2']);
        Configs::where('key', 'sorting_Albums_order')->update(['confidentiality' => '2']);
    }
}
