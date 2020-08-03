<?php

declare(strict_types=1);

namespace App\ModelFunctions;

use App\Configs;
use App\Exceptions\NotLoggedInException;
use App\Exceptions\RequestAdminDataException;
use App\Exceptions\UserNotFoundException;
use App\Logs;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SessionFunctions
{
    private $user_data;

    /**
     * Return true if the user is logged in (Admin or User)
     * Return false if it is Guest access.
     */
    public function is_logged_in(): bool
    {
        return Auth::check();
    }

    /**
     * Return true if the user is logged in and an admin.
     */
    public function is_admin(): bool
    {
        return Auth::user() && Auth::user()->isAdmin();
    }

    public function can_upload(): bool
    {
        return Auth::user()->isAdmin() || $this->getUserData()->upload;
    }

    /**
     * Return the current ID of the user
     * what happens when UserID is not set? :p.
     *
     * @throws NotLoggedInException
     */
    public function id(): int
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        return Auth::user()->id;
    }

    /**
     * Return User object given a positive ID.
     */
    private function accessUserData(): User
    {
        $this->user_data = Auth::user();

        if (!$this->user_data) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified user (' . $id . ')');
            throw new UserNotFoundException($id);
        }

        if ($this->user_data->isAdmin()) {
            Logs::error(__METHOD__, (string) __LINE__, 'Trying to get a User from Admin ID.');
            throw new RequestAdminDataException();
        }

        return $this->user_data;
    }

    /**
     * Return User object and cache the result.
     */
    public function getUserData(): User
    {
        return $this->user_data ?? $this->accessUserData();
    }

    /**
     * Return true if the currently logged in user is the one provided
     * (or if that user is Admin).
     */
    public function is_current_user(int $userId): bool
    {
        return Auth::check() && (Auth::user()->id === $userId || Auth::user()->isAdmin());
    }

    /**
     * Sets the session values when no there is no username and password in the database.
     *
     * @return bool returns true when no login was found
     */
    public function noLogin(): bool
    {
        $configs = Configs::get();

        // Check if login credentials exist and login if they don't
        if (
            isset($configs['username'], $configs['password'])
            && $configs['username'] === ''
            && $configs['password'] === ''
        ) {
            Session::put('login', true);
            Session::put('UserID', 0);

            return true;
        }

        return false;
    }

    /**
     * Given a username, password and ip (for logging), try to log the user.
     * returns true if succeed
     * returns false if fail.
     */
    public function log_as_user(string $username, string $password, string $ip): bool
    {
        $attempt = Auth::attempt([
            'username' => $username,
            'password' => $password,
        ]);

        if (!$attempt) {
            // There might a possibility the user couldn't be authenticated because it is the one migrated from Configs
            // where the username was hashed as well which makes things a litlle bit more complicated. So we are gonna
            // self heal the credentials to makes everything better.
            $user = User::where('type', User::ADMIN_TYPE)->get()->first();
            if ($user && Hash::check($username, $user->username) && Hash::check($password, $user->password)) {
                $user->username = $username;
                $user->save();
                Auth::login($user, true);
                $attempt = true;
            }
        }

        if ($attempt) {
            $user = Auth::user();
            Session::put('login', true);
            Session::put('UserID', $user->id);
            Logs::notice(__METHOD__, (string) __LINE__, 'User (' . $username . ') has logged in from ' . $ip);
            $this->user_data = $user;
        }

        return $attempt;
    }

    /**
     * Given an albumID, check if it exists in the visible_albums session variable.
     *
     * @param $albumID
     */
    public function has_visible_album(int $albumID): bool
    {
        if (!Session::has('visible_albums')) {
            return false;
        }

        $visible_albums = Session::get('visible_albums');
        $visible_albums = \explode('|', $visible_albums);

        return \in_array($albumID, $visible_albums, true);
    }

    /**
     * Add new album to the visible_albums session variable.
     *
     * @param $albumID
     */
    public function add_visible_album(int $albumID): void
    {
        $visible_albums = '';
        if (Session::has('visible_albums')) {
            $visible_albums = Session::get('visible_albums');
        }

        $visible_albums = \explode('|', $visible_albums);
        if (!\in_array($albumID, $visible_albums, true)) {
            $visible_albums[] = $albumID;
        }

        $visible_albums = \implode('|', $visible_albums);
        Session::put('visible_albums', $visible_albums);
    }

    /**
     * Log out the current user.
     */
    public function logout(): void
    {
        $this->user_data = null;
        Session::flush();
        Auth::logout();
    }
}
