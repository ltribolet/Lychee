<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Assets\Helpers;
use App\ModelFunctions\SessionFunctions;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Set the Login information of the Lychee configuration
     * Either they are not already set and we directly bcrypt the parameters
     * or the current username and password are compared and changed if successful.
     *
     * To be noted this function will change the CONFIG table if used by admin
     * or the USER table if used by any other user
     */
    public function setLogin(Request $request, UserService $service): string
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        /** @var User|null $adminUser */
        $adminUser = User::where('type', User::ADMIN_TYPE)->first();

        if (!$adminUser) {
            $create = $service->createAdmin($request['username'], $request['password']);
            \app(SessionFunctions::class)->log_as_user($request['username'], $request['password'], $request->ip());

            return $create;
        }

        return $service->updateUser($adminUser, $request['username'], true, false, $request['password']);
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

        $locale = Arr::has(Config::get('app.locales'), $request['lang']);

        if (!$locale) {
            Logs::error(__METHOD__, (string) __LINE__, 'Could not update settings. Unknown lang.');

            return 'false';
        }

        return Configs::set('lang', $request['lang']) ? 'true' : 'false';
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
            ++$i;
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
        $css = $css ?? '';

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
     *
     * @deprecated
     */
    public function getAll(): Collection
    {
        return Configs::orderBy('cat', 'ASC')->get();
    }

    /**
     * Return ALL the settings. This is not filtered!
     * Fortunately this is behind an admin middlewear.
     * This is used in the advanced settings part.
     */
    public function index(): Collection
    {
        return Configs::orderBy('cat', 'ASC')->get();
    }

    /**
     * Get a list of settings and save them in the database
     * if the associated key exists.
     *
     * @deprecated
     */
    public function saveAll(Request $request): string
    {
        $no_error = true;
        foreach ($request->except(['_token', 'function', '/api/Settings::saveAll']) as $key => $value) {
            $value = $value ?? '';
            $no_error &= Configs::set($key, $value);
        }

        return $no_error ? 'true' : 'false';
    }

    /**
     * Get a list of settings and save them in the database
     * if the associated key exists.
     */
    public function update(Request $request): string
    {
        $no_error = true;
        foreach ($request->except(['_token', 'function', '/api/Settings::saveAll']) as $key => $value) {
            $value = $value ?? '';
            $no_error &= Configs::set($key, $value);
        }

        return $no_error ? 'true' : 'false';
    }
}
