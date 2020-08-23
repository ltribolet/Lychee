<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class ViewController extends Controller
{
    /**
     * Just the constructor
     * This also shows how to apply a middlewear directly in a controller.
     *
     * ViewController constructor.
     */
    public function __construct()
    {
        $this->middleware([]);
    }

    /**
     * View is only used when sharing a single picture.
     *
     * @return View|void
     */
    public function view(Request $request)
    {
        $request->validate([
            'p' => 'required',
        ]);

        $photo = Photo::find($request->get('p'));

        if ($photo === null) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not find photo in database');

            \abort(404);
        }

        // is the picture public ?
        $public = $photo->public === '1';

        // is the album (if exist) public ?
        if ($photo->album_id !== null) {
            $public = $photo->album->public === '1' || $public;
        }
        // return 403 if not allowed
        if (! $public) {
            \abort(403);
        }

        if ($photo->medium === '1') {
            $dir = 'medium';
        } else {
            $dir = 'big';
        }

        $title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));

        $url = \config('app.url') . $request->server->get('REQUEST_URI');
        $picture = \config('app.url') . '/uploads/' . $dir . '/' . $photo->url;

        return \view('view', \compact('url', 'photo', 'picture', 'title'));
    }
}
