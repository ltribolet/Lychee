<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\SaveUserRequest;
use App\Logs;
use App\Services\UserService;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware([]);
    }

    public function list(): Collection
    {
        return User::all();
    }

    /**
     * Save modification done to a user.
     * Note that an admin can change the password of a user at will.
     */
    public function save(SaveUserRequest $request, UserService $service): string
    {
        $user = User::find($request['id']);
        $loggedUserId = Auth::user()->id;

        $password = null;
        if ($request->has('password') && $request['password'] !== '') {
            $password = $request['password'];
        }

        $update = $service->updateUser(
            $user,
            $request['username'],
            $request['upload'] === '1',
            $request['lock'] === '1',
            $password
        );

        // Avoid logging out the user currently logged in.
        if ($update === 'true' && $loggedUserId === $user->id) {
            Auth::login($user, true);

            $request->session()->put([
                'password_hash' => $user->getAuthPassword(),
            ]);
        }

        return $update;
    }

    /**
     * Delete a user.
     * FIXME: What happen to the albums owned ?
     *
     * @throws Exception
     */
    public function delete(Request $request): string
    {
        $request->validate([
            'id' => 'required',
        ]);

        $user = User::find($request['id']);
        if ($user === null) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not find specified user ' . $request['id']);

            return 'false';
        }

        return $user->delete() ? 'true' : 'false';
    }

    public function create(CreateUserRequest $request, UserService $service): string
    {
        return $service->createUser(
            $request['username'],
            $request['password'],
            $request['upload'] === '1',
            $request['lock'] === '1'
        );
    }
}
