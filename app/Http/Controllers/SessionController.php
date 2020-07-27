<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Assets\Helpers;
use App\Configs;
use App\Locale\Lang;
use App\Logs;
use App\Metadata\GitHubFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    /**
     * @var ConfigFunctions
     */
    private $configFunctions;

    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    /**
     * @var GitHubFunctions
     */
    private $gitHubFunctions;

    public function __construct(
        ConfigFunctions $configFunctions,
        SessionFunctions $sessionFunctions,
        GitHubFunctions $gitHubFunctions
    ) {
        $this->configFunctions = $configFunctions;
        $this->sessionFunctions = $sessionFunctions;
        $this->gitHubFunctions = $gitHubFunctions;
    }

    /**
     * First function being called via AJAX.
     *
     * @return array<mixed>|bool (array containing config information or killing the session)
     */
    public function init()
    {
        $logged_in = $this->sessionFunctions->is_logged_in();

        // Return settings
        $return = [];

        // we are using api_V2
        $return['api_V2'] = true;
        // Lychee-laravel does have sub albums
        $return['sub_albums'] = true;

        // Check if login credentials exist and login if they don't
        if ($this->sessionFunctions->noLogin() === true || $logged_in === true) {
            // we the the UserID (it is set to 0 if there is no login/password = admin)
            $user_id = $this->sessionFunctions->id();

            if ($user_id === 0) {
                $return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');
                $return['admin'] = true;
                // not necessary
                $return['upload'] = true;

                $return['config'] = $this->configFunctions->admin();

                $return['config']['location'] = \base_path('public/');
            } else {
                $user = User::find($user_id);

                if ($user === null) {
                    Logs::notice(__METHOD__, (string) __LINE__, 'UserID ' . $user_id . ' not found!');

                    return $this->logout();
                }
                $return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');

                $return['config'] = $this->configFunctions->public();
                // can user change his password
                $return['lock'] = ($user->lock === '1');
                // can user upload ?
                $return['upload'] = ($user->upload === '1');
                $return['username'] = $user->username;
            }

            // here we say whether we looged in because there is no login/password or if we actually entered a login/password
            $return['config']['login'] = $logged_in;
        } else {
            // Logged out
            $return['config'] = $this->configFunctions->public();
            if (Configs::get_value('hide_version_number', '1') !== '0') {
                $return['config']['version'] = '';
            }
            $return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDOUT');
        }

        $deviceType = Helpers::getDeviceType();
        // UI behaviour needs to be slightly modified if client is a TV
        $return['config_device'] = $this->configFunctions->get_config_device($deviceType);

        // we also return the local
        $return['locale'] = Lang::get_lang(Configs::get_value('lang'));

        $return['update_json'] = 0;
        $return['update_available'] = false;

        $this->gitHubFunctions->checkUpdates($return);

        return $return;
    }

    /**
     * Login tentative.
     */
    public function login(Request $request): string
    {
        $request->validate([
            'user' => 'required',
            'password' => 'required',
        ]);

        // No login
        if ($this->sessionFunctions->noLogin() === true) {
            Logs::warning(__METHOD__, (string) __LINE__, 'DEFAULT LOGIN!');

            return 'true';
        }

        // this is probably sensitive to timing attacks...
        if ($this->sessionFunctions->log_as_admin($request['user'], $request['password'], $request->ip()) === true) {
            return 'true';
        }

        if ($this->sessionFunctions->log_as_user($request['user'], $request['password'], $request->ip()) === true) {
            return 'true';
        }

        Logs::error(
            __METHOD__,
            (string) __LINE__,
            'User (' . $request['user'] . ') has tried to log in from ' . $request->ip()
        );

        return 'false';
    }

    /**
     * Unset the session values.
     */
    public function logout(): string
    {
        $this->sessionFunctions->logout();

        return 'true';
    }

    /**
     * Show the session values.
     */
    public function show(): void
    {
        \dd(Session::all());
    }
}
