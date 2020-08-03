<?php

declare(strict_types=1);

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function createAdmin(string $username, string $password): string
    {
        $user = new User();
        $user->upload = true;
        $user->lock = false;
        $user->username = $username;
        $user->type = User::ADMIN_TYPE;
        $user->password = Hash::make($password);

        return $user->save() ? 'true' : 'false';
    }

    public function createUser(
        string $username,
        string $password,
        bool $upload = true,
        bool $lock = false
    ): string {
        $user = new User();
        $user->upload = $upload;
        $user->lock = $lock;
        $user->username = $username;
        $user->type = User::USER_TYPE;
        $user->password = Hash::make($password);

        return $user->save() ? 'true' : 'false';
    }

    public function updateUser(
        User $user,
        string $username,
        bool $upload = false,
        bool $lock = false,
        ?string $password = null
    ): string {
        $user->username = $username;
        $user->upload = $upload;
        $user->lock = $lock;
        if (!empty($password)) {
            $user->password = Hash::make($password);
        }

        return $user->save() ? 'true' : 'false';
    }
}
