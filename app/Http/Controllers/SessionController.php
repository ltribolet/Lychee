<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Assets\Helpers;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SessionController extends Controller
{
    private ConfigFunctions $configFunctions;
    private SessionFunctions $sessionFunctions;

    public function __construct(ConfigFunctions $configFunctions, SessionFunctions $sessionFunctions)
    {
        $this->configFunctions = $configFunctions;
        $this->sessionFunctions = $sessionFunctions;
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
        if ($logged_in === true) {
            if ($this->sessionFunctions->is_admin()) {
                $return['status'] = Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN');
                $return['admin'] = true;
                // not necessary
                $return['upload'] = true;

                $return['config'] = $this->configFunctions->admin();

                $return['config']['location'] = \base_path('public/');
            } else {
                $user = Auth::user();

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
            // Guest | Public
            $return['config'] = $this->configFunctions->public();
            if (Configs::get_value('hide_version_number', '1') !== '0') {
                $return['config']['version'] = '';
            }

            // Do we have at least one user?
            $isOriginalUser = User::count('id') > 0;

            $return['config']['login'] = false;
            $return['status'] = $isOriginalUser ?
                Config::get('defines.status.LYCHEE_STATUS_LOGGEDOUT') :
                Config::get('defines.status.LYCHEE_STATUS_LOGGEDIN')
            ;
        }

        $deviceType = Helpers::getDeviceType();
        // UI behaviour needs to be slightly modified if client is a TV
        $return['config_device'] = $this->configFunctions->get_config_device($deviceType);

        // we also return the local
        $return['locale'] = \trans('messages');

        $return['update_json'] = 0;
        $return['update_available'] = false;

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
}
