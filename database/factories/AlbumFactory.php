<?php

declare(strict_types=1);

use App\Models\Album;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Album::class, function (Faker $faker) {
    return [
        'title' => $faker->realText(20),
        'parent_id' => null,
        'owner_id' => 1,
        'min_takestamp' => Carbon::create(2020, 1, 1),
        'max_takestamp' => Carbon::create(2020, 1, 1),
        'description' => $faker->sentence(10),
        'public' => false,
        'created_at' => Carbon::now(),
        'full_photo' => true,
        'visible_hidden' => true,
        'downloadable' => false,
        'share_button_visible' => false,
        'password' => null,
        'license' => 'none',
    ];
});
