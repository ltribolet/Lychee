<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Assets\Helpers;
use App\Configs;
use App\Locale\Lang;
use App\Logs;
use App\ModelFunctions\SessionFunctions;
use App\Response;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    public function __construct(SessionFunctions $sessionFunctions)
    {
        $this->sessionFunctions = $sessionFunctions;
    }

    /**
     * Set the Login information of the Lychee configuration
     * Either they are not already set and we directly bcrypt the parameters
     * or the current username and password are compared and changed if successful.
     *
     * To be noted this function will change the CONFIG table if used by admin
     * or the USER table if used by any other user
     */
    public function setLogin(Request $request): string
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $configs = Configs::get();
        $oldPassword = $request->has('oldPassword') ? $request['oldPassword']
            : '';
        $oldUsername = $request->has('oldUsername') ? $request['oldUsername']
            : '';

        if ($configs['password'] === '' && $configs['username'] === '') {
            Configs::set('username', \bcrypt($request['username']));
            Configs::set('password', \bcrypt($request['password']));

            return 'true';
        }

        if ($this->sessionFunctions->is_admin()) {
            if ($configs['password'] === ''
                || Hash::check($oldPassword, $configs['password'])
            ) {
                Configs::set('username', \bcrypt($request['username']));
                Configs::set('password', \bcrypt($request['password']));

                return 'true';
            }

            return Response::error('Current password entered incorrectly!');
        } elseif ($this->sessionFunctions->is_logged_in()) {
            $id = $this->sessionFunctions->id();

            // this is probably sensitive to timing attacks...
            $user = User::find($id);

            if ($user === null) {
                Logs::error(__METHOD__, (string) __LINE__, 'User (' . $id . ') does not exist!');

                return Response::error('Could not find User.');
            }

            if ($user->lock) {
                Logs::notice(__METHOD__, (string) __LINE__,
                    'Locked user (' . $user->username
                    . ') tried to change his identity from ' . $request->ip());

                return Response::error('Locked account!');
            }

            if (User::where('username', '=', $request['username'])->where('id', '!=', $id)->count()) {
                Logs::notice(__METHOD__, (string) __LINE__,
                    'User (' . $user->username
                    . ') tried to change his identity to ' . $request['username'] . ' from ' . $request->ip());

                return Response::error('Username already exists.');
            }

            if ($user->username === $oldUsername
                && Hash::check($oldPassword, $user->password)
            ) {
                Logs::notice(__METHOD__, (string) __LINE__,
                    'User (' . $user->username . ') changed his identity for ('
                    . $request['username'] . ') from ' . $request->ip());
                $user->username = $request['username'];
                $user->password = \bcrypt($request['password']);

                return $user->save() ? 'true' : 'false';
            }
            Logs::notice(__METHOD__, (string) __LINE__, 'User (' . $user->username
                    . ') tried to change his identity from ' . $request->ip());

            return Response::error('Old username or password entered incorrectly!');
        }
    }

    /**
     * Define the default sorting type
     * TODO: make it configurable by album.
     */
    public function setSorting(Request $request): string
    {
        $request->validate([
            'typeAlbums' => 'required|string',
            'orderAlbums' => 'required|string',
            'typePhotos' => 'required|string',
            'orderPhotos' => 'required|string',
        ]);

        Configs::set('sorting_Photos_col', $request['typePhotos']);
        Configs::set('sorting_Photos_order', $request['orderPhotos']);
        Configs::set('sorting_Albums_col', $request['typeAlbums']);
        Configs::set('sorting_Albums_order', $request['orderAlbums']);

        return 'true';
    }

    /**
     * Set the lang used by the Lychee installation.
     */
    public function setLang(Request $request): string
    {
        $request->validate([
            'lang' => 'required|string',
        ]);

        $lang_available = Lang::get_lang_available();
        for ($i = 0; $i < \count($lang_available); $i++) {
            if ($request['lang'] === $lang_available[$i]) {
                return Configs::set('lang', $lang_available[$i]) ? 'true'
                    : 'false';
            }
        }

        Logs::error(__METHOD__, (string) __LINE__, 'Could not update settings. Unknown lang.');

        return 'false';
    }

    /**
     * Set the layout of the albums
     * 0: squares
     * 1: flickr justified
     * 2: flickr unjustified.
     */
    public function setLayout(Request $request): string
    {
        $request->validate([
            'layout' => 'required|string',
        ]);

        return Configs::set('layout', $request['layout']) ? 'true' : 'false';
    }

    /**
     * Set the dropbox key for the API.
     */
    public function setDropboxKey(Request $request): string
    {
        $request->validate([
            'key' => 'string|nullable',
        ]);

        return Configs::set('dropbox_key', $request['key']) ? 'true' : 'false';
    }

    /**
     * Allow public user to use the search function.
     */
    public function setPublicSearch(Request $request): string
    {
        $request->validate([
            'public_search' => 'required|string',
        ]);

        if ($request['public_search'] === '1') {
            return Configs::set('public_search', '1') ? 'true' : 'false';
        }

        return Configs::set('public_search', '0') ? 'true' : 'false';
    }

    /**
     * Show image overlay by default or not
     * (white text in the bottom right corner).
     */
    public function setImageOverlay(Request $request): string
    {
        $request->validate([
            'image_overlay' => 'required|string',
        ]);

        if ($request['image_overlay'] === '1') {
            return Configs::set('image_overlay', '1') ? 'true' : 'false';
        }

        return Configs::set('image_overlay', '0') ? 'true' : 'false';
    }

    /**
     * Select the image overlay used:
     * exif: exif information
     * desc: description of the photo
     * takedate: date of the photo (and dimensions?).
     */
    public function setImageOverlayType(Request $request): string
    {
        $request->validate([
            'image_overlay_type' => 'required|string',
        ]);

        return Configs::set('image_overlay_type', $request['image_overlay_type']) ? 'true' : 'false';
    }

    /**
     * Define the default license of the pictures.
     */
    public function setDefaultLicense(Request $request): string
    {
        $request->validate([
            'license' => 'required|string',
        ]);

        // add this to the list
        $licenses = Helpers::get_all_licenses();
        $i = 0;
        while ($i < \count($licenses)) {
            if ($licenses[$i] === $request['license']) {
                return Configs::set('default_license', $request['license'])
                    ? 'true' : 'false';
            }
            $i++;
        }

        Logs::error(__METHOD__, (string) __LINE__, 'Could not find the submitted license');

        return 'false';
    }

    /**
     * Enable display of photo coordinates on map.
     */
    public function setMapDisplay(Request $request): string
    {
        $request->validate([
            'map_display' => 'required|string',
        ]);

        if ($request['map_display'] === '1') {
            return Configs::set('map_display', '1') ? 'true' : 'false';
        }

        return Configs::set('map_display', '0') ? 'true' : 'false';
    }

    /**
     * Enable display of photos on map for public albums.
     */
    public function setMapDisplayPublic(Request $request): string
    {
        $request->validate([
            'map_display_public' => 'required|string',
        ]);

        if ($request['map_display_public'] === '1') {
            return Configs::set('map_display_public', '1') ? 'true' : 'false';
        }

        return Configs::set('map_display_public', '0') ? 'true' : 'false';
    }

    /**
     * Set provider of OSM map tiles.
     */
    public function setMapProvider(Request $request): string
    {
        $request->validate([
            'map_provider' => 'required|string',
        ]);

        return Configs::set('map_provider', $request['map_provider']) ? 'true' : 'false';
    }

    /**
     * Enable display of photos of subalbums on map.
     */
    public function setMapIncludeSubalbums(Request $request): string
    {
        $request->validate([
            'map_include_subalbums' => 'required|string',
        ]);

        if ($request['map_include_subalbums'] === '1') {
            return Configs::set('map_include_subalbums', '1') ? 'true' : 'false';
        }

        return Configs::set('map_include_subalbums', '0') ? 'true' : 'false';
    }

    /**
     * Enable decoding of GPS data into location names.
     */
    public function setLocationDecoding(Request $request): string
    {
        $request->validate([
            'location_decoding' => 'required|string',
        ]);

        return Configs::set('location_decoding', $request['location_decoding']) ? 'true' : 'false';
    }

    /**
     * Enable display of location name.
     */
    public function setLocationShow(Request $request): string
    {
        $request->validate([
            'location_show' => 'required|string',
        ]);

        return Configs::set('location_show', $request['location_show']) ? 'true' : 'false';
    }

    /**
     * Enable display of location name for public albums.
     */
    public function setLocationShowPublic(Request $request): string
    {
        $request->validate([
            'location_show_public' => 'required|string',
        ]);

        return Configs::set('location_show_public', $request['location_show_public']) ? 'true' : 'false';
    }

    /**
     * take the css input text and put it into dist/user.css
     * this allow admins to actually personalize the look of their installation.
     */
    public function setCSS(Request $request): string
    {
        $request->validate(['css' => 'nullable|string']);
        $css = $request->get('css');
        $css = $css === null ? '' : $css;

        if (!Storage::disk('dist')->put('user.css', $css)) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not save css.');

            return 'false';
        }

        return 'true';
    }

    /**
     * Return ALL the settings. This is not filtered!
     * Fortunately this is behind an admin middlewear.
     * This is used in the advanced settings part.
     */
    public function getAll(): Collection
    {
        return Configs::orderBy('cat', 'ASC')->get();
    }

    /**
     * Get a list of settings and save them in the database
     * if the associated key exists.
     */
    public function saveAll(Request $request): string
    {
        $no_error = true;
        foreach (
            $request->except(['_token', 'function', '/api/Settings::saveAll']) as $key => $value
        ) {
            $value = $value === null ? '' : $value;
            $no_error &= Configs::set($key, $value);
        }

        return $no_error ? 'true' : 'false';
    }
}
