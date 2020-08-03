<?php

declare(strict_types=1);

use App\Configs;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MigrateAdminToUsersTable extends Migration
{
    private const ADMIN_TYPE = 'admin';

    public function up(): void
    {
        $usernameConfig = Configs::firstWhere('key', 'username');
        $username = \optional($usernameConfig)->value;
        $passwordConfig = Configs::firstWhere('key', 'password');
        $password = \optional($passwordConfig)->value;

        if (!$username || !$password) {
            return;
        }

        // There can be only one admin at any given time. For now. Maybe?
        $user = User::where('type', self::ADMIN_TYPE)
            ->first();

        // It means we already migrated this user
        if ($user) {
            return;
        }

        $insert = DB::table('users')->insert([
            [
                'username' => $username,
                'password' => $password,
                'type' => self::ADMIN_TYPE,
                'upload' => true,
                'created_at' => Carbon::now(),
            ],
        ]);

        if (!$insert) {
            Log::error('Could not insert new User');

            return;
        }

        $usernameConfig->delete();
        $passwordConfig->delete();
    }

    public function down(): void
    {
        /** @var User $user */
        $user = User::where('type', self::ADMIN_TYPE)->first();

        // Can't do anything here.
        if (!$user) {
            return;
        }

        $insert = DB::table('configs')->insert([
            [
                'key' => 'username',
                'value' => $user->username,
                'cat' => 'Admin',
                'type_range' => 'string_required',
                'confidentiality' => '4',
            ],
            [
                'key' => 'password',
                'value' => $user->password,
                'cat' => 'Admin',
                'type_range' => 'string_required',
                'confidentiality' => '4',
            ],
        ]);

        if (!$insert) {
            Log::error('Could not insert new Configs');

            return;
        }

        $user->delete();
    }
}
