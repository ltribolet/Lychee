<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PanicAttack;

class EnvController extends Controller
{
    public function view(Request $request): View
    {
        $env = '';
        $exists = false;

        if (\file_exists(\base_path('.env'))) {
            $env = \file_get_contents(\base_path('.env'));
            $exists = true;
        } else {
            // @codeCoverageIgnoreStart
            $env = \file_get_contents(\base_path('.env.example'));
            $exists = false;
            // @codeCoverageIgnoreEnd
        }

        if ($request->has('envConfig')) {
            $env = \str_replace("\r", '', $request->get('envConfig'));
            try {
                \file_put_contents(\base_path('.env'), $env, LOCK_EX);
            } catch (\Throwable $e) {
                $oups = new PanicAttack();
                $oups->handle($e->getMessage());
            }
            $exists = true;
        }

        return \view('install.env', [
            'title' => 'Lychee-installer',
            'step' => 3,
            'env' => $env,
            'exists' => $exists,
        ]);
    }
}
