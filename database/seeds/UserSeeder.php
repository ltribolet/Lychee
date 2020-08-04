<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \factory(User::class)->create([
            'username' => 'lychee',
            'password' => Hash::make('lychee'),
            'upload' => true,
            'lock' => false,
            'remember_token' => '',
            'created_at' => Carbon::now(),
            'type' => User::ADMIN_TYPE,
        ]);
    }
}
