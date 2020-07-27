<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\ControllerFunctions\Install\DefaultConfig;
use App\ControllerFunctions\Install\PermissionsChecker;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class PermissionsController extends Controller
{
    /**
     * @var PermissionsChecker
     */
    protected $permissions;
    /**
     * @var DefaultConfig
     */
    protected $config;

    public function __construct(PermissionsChecker $checker, DefaultConfig $config)
    {
        $this->permissions = $checker;
        $this->config = $config;
    }

    public function view(): View
    {
        $perms = $this->permissions->check($this->config->get_permissions());

        return \view('install.permissions', [
            'title' => 'Lychee-installer',
            'step' => 2,
            'permissions' => $perms['permissions'],
            'errors' => $perms['errors'],
            'windows' => $this->permissions->is_win(),
        ]);
    }
}
