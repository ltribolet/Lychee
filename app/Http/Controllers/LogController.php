<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LogController extends Controller
{
    /**
     * @return mixed
     */
    public function list(string $order = 'DESC')
    {
        return Logs::orderBy('id', $order)->get();
    }

    /**
     * display the Logs.
     *
     * @return View|string
     */
    public function display()
    {
        if (Logs::count() === 0) {
            return 'Everything looks fine, Lychee has not reported any problems!';
        }
        $logs = $this->list();

        return \view('logs.list', ['logs' => $logs]);
    }

    /**
     * Empty the log table.
     */
    public static function clear(): string
    {
        DB::table('logs')->truncate();

        return 'Log cleared';
    }

    /**
     * This function does pretty much the same as clear but only does it on notice
     * and also keeps the log of the loggin attempts.
     */
    public static function clearNoise(): string
    {
        Logs::where('function', '!=', 'App\Http\Controllers\SessionController::login')->
            where('type', '=', 'notice')->delete();

        return 'Log Noise cleared';
    }
}
