<?php

declare(strict_types=1);

use App\Configs;
use App\Photo;
use Illuminate\Database\Migrations\Migration;

class UpdateLicences extends Migration
{
    /**
     * Update the fields.
     *
     * @param array<array<string>> $default_values
     */
    private function update_fields(array &$default_values): void
    {
        foreach ($default_values as $value) {
            Configs::updateOrCreate(['key' => $value['key']],
                [
                    'cat' => $value['cat'],
                    'type_range' => $value['type_range'],
                    'confidentiality' => $value['confidentiality'],
                ]);
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        defined('LICENSE') or define('LICENSE', 'license');

        $default_values = [
            [
                'key' => 'default_license',
                'value' => 'none',
                'cat' => 'Gallery',
                'type_range' => LICENSE,
                'confidentiality' => '2',
            ],
        ];

        $this->update_fields($default_values);

        // Get all CC licences
        $photos = Photo::where('license', 'like', 'CC-%')->get();
        if (count($photos) === 0) {
            return;
        }
        foreach ($photos as $photo) {
            $photo->license .= '-4.0';
            $photo->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all CC licences
        $photos = Photo::where('license', 'like', 'CC-%')->get();
        if (count($photos) === 0) {
            return;
        }
        foreach ($photos as $photo) {
            // Delete version
            $photo->license = mb_substr($photo->license, 0, -4);
            $photo->save();
        }
    }
}
