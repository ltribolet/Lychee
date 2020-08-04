<?php

declare(strict_types=1);

use App\Models\Logs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseLengthPhotoType extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->string('type', 30)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Logs::warning(__FUNCTION__, (string) __LINE__, 'There is no going back for ' . self::class . '!');
    }
}
