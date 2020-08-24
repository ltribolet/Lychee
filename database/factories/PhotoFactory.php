<?php

declare(strict_types=1);

use App\Models\Photo;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

// Move and/or refactor, for now using for tests only
if (! function_exists('humanFileSize')) {
    function humanFileSize(int $bytes, ?int $dec = 2): string
    {
        if ($bytes === 0) {
            return '0.0 B';
        }

        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = \floor((\mb_strlen((string) $bytes) - 1) / 3);

        return \sprintf("%.{$dec}f ", $bytes / (1024 ** $factor)) . @$size[$factor];
    }
}

$factory->define(Photo::class, function (Faker $faker) {
    $fakeImage = $faker->image(\sys_get_temp_dir(), 6000, 4000);
    $imageUrl = File::basename($fakeImage);
    $size = humanFileSize(File::size($fakeImage) ?: 0);

    return [
        'title' => $faker->realText(20),
        'description' => $faker->sentence(10),
        'url' => $imageUrl,
        'tags' => '',
        'public' => false,
        'owner_id' => 0,
        'type' => 'image/jpeg',
        'width' => 6000,
        'height' => 4000,
        'size' => $size,
        'iso' => '',
        'aperture' => '',
        'make' => '',
        'model' => '',
        'lens' => '',
        'shutter' => '',
        'focal' => '',
        'latitude' => null,
        'longitude' => null,
        'altitude' => null,
        'imgDirection' => null,
        'location' => null,
        'takestamp' => $faker->dateTimeThisDecade,
        'star' => false,
        'thumbUrl' => $imageUrl,
        'livePhotoUrl' => null,
        'album_id' => null,
        'checksum' => $faker->sha1,
        'livePhotoChecksum' => null,
        'license' => 'none',
        'created_at' => Carbon::create(2020, 1, 1),
        'medium' => '1620x1080',
        'medium2x' => '3240x2160',
        'small' => '540x360',
        'small2x' => '1080x720',
        'thumb2x' => true,
        'livePhotoContentID' => null,
    ];
});
