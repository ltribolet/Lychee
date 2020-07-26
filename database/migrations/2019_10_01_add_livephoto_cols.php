<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LivephotoCols extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photos', function ($table): void {
            $table->string('livePhotoUrl')->default(null)->after('thumbURL')->nullable();
        });

        Schema::table('photos', function ($table): void {
            $table->string('livePhotoContentID')->default(null)->after('thumb2x')->nullable();
        });

        Schema::table('photos', function ($table): void {
            $table->string('livePhotoChecksum', 40)->default(null)->after('checksum')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->dropColumn('livePhotoContentID');
        });
        Schema::table('photos', function (Blueprint $table): void {
            $table->dropColumn('livePhotoUrl');
        });
        Schema::table('photos', function (Blueprint $table): void {
            $table->dropColumn('livePhotoChecksum');
        });
    }
}
