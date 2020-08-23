<?php

declare(strict_types=1);

use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PhotosFix extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->fix_thumbs();
        $this->image_direction();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Logs::warning(__FUNCTION__, (string) __LINE__, 'There is no going back! HUE HUE HUE');
    }

    private function fix_thumbs(): void
    {
        // from fix_thumb2x_default
        Photo::where('thumbUrl', '=', '')
            ->where('thumb2x', '=', '1')
            ->update([
                'thumb2x' => 0,
            ]);
        Schema::table('photos', function (Blueprint $table): void {
            $table->boolean('thumb2x')->default(false)->change();
        });
    }

    private function image_direction(): void
    {
        // migration from imageDirection
        if (! Schema::hasColumn('photos', 'imgDirection')) {
            Schema::table('photos', function (Blueprint $table): void {
                $table->decimal('imgDirection', 10, 4)->default(null)
                    ->after('altitude')->nullable();
            });
        }
    }
}
