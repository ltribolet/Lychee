<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Install Routes
|--------------------------------------------------------------------------
|
| Here is where you can register install routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "install" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'api/settings'], function (): void {
    Route::put('sorting', 'SettingsController@setSorting');
    Route::put('lang', 'SettingsController@setLang');
    Route::put('layout', 'SettingsController@setLayout');
    Route::put('public-search', 'SettingsController@setPublicSearch');
    Route::put('image-overlay', 'SettingsController@setImageOverlay');
    Route::put('default-license', 'SettingsController@setDefaultLicense');
    Route::put('map-display', 'SettingsController@setMapDisplay');
    Route::put('map-display-public', 'SettingsController@setMapDisplayPublic');
    Route::put('map-provider', 'SettingsController@setMapProvider');
    Route::put('map-include-sub-albums', 'SettingsController@setMapIncludeSubalbums');
    Route::put('location-decoding', 'SettingsController@setLocationDecoding');
    Route::put('location-show', 'SettingsController@setLocationShow');
    Route::put('location-show-public', 'SettingsController@setLocationShowPublic');
    Route::put('css', 'SettingsController@setCSS');
    Route::put('overlay-type', 'SettingsController@setImageOverlayType');
    Route::put('dropbox-key', 'SettingsController@setDropboxKey');

    Route::get('/', 'SettingsController@index');
    Route::put('/', 'SettingsController@update');
});

Route::group(['prefix' => 'api/users'], function (): void {
    Route::put('{user}', 'UserController@update')->name('users.update');
    Route::delete('{user}', 'UserController@destroy')->name('users.destroy');
    Route::post('/', 'UserController@create')->name('users.store');
});

Route::post('/api/import/server', 'ImportController@server');

Route::post('/api/logs', 'LogController@index');
Route::delete('/api/logs', 'LogController@destroy');
Route::delete('/api/logs/noise', 'LogController@detroyNoise');

/*
 * Legacy APIS
 */
Route::post('/api/Settings::setSorting', 'SettingsController@setSorting');
Route::post('/api/Settings::setLang', 'SettingsController@setLang');
Route::post('/api/Settings::setLayout', 'SettingsController@setLayout');
Route::post('/api/Settings::setPublicSearch', 'SettingsController@setPublicSearch');
Route::post('/api/Settings::setImageOverlay', 'SettingsController@setImageOverlay');
Route::post('/api/Settings::setDefaultLicense', 'SettingsController@setDefaultLicense');
Route::post('/api/Settings::setMapDisplay', 'SettingsController@setMapDisplay');
Route::post('/api/Settings::setMapDisplayPublic', 'SettingsController@setMapDisplayPublic');
Route::post('/api/Settings::setMapProvider', 'SettingsController@setMapProvider');
Route::post('/api/Settings::setMapIncludeSubalbums', 'SettingsController@setMapIncludeSubalbums');
Route::post('/api/Settings::setLocationDecoding', 'SettingsController@setLocationDecoding');
Route::post('/api/Settings::setLocationShow', 'SettingsController@setLocationShow');
Route::post('/api/Settings::setLocationShowPublic', 'SettingsController@setLocationShowPublic');
Route::post('/api/Settings::setCSS', 'SettingsController@setCSS');
Route::post('/api/Settings::getAll', 'SettingsController@getAll');
Route::post('/api/Settings::saveAll', 'SettingsController@saveAll');
Route::post('/api/Settings::setOverlayType', 'SettingsController@setImageOverlayType');
Route::post('/api/Settings::setDropboxKey', 'SettingsController@setDropboxKey');

Route::post('/api/User::Save', 'UserController@save');
Route::post('/api/User::Delete', 'UserController@delete');
Route::post('/api/User::Create', 'UserController@create');

Route::post('/api/Import::server', 'ImportController@server');

Route::get('/Logs', 'LogController@display');
Route::post('/api/Logs', 'LogController@display');
Route::get('/api/Logs::clear', 'LogController@clear');
Route::post('/api/Logs::clear', 'LogController@clear');
Route::post('/api/Logs::clearNoise', 'LogController@clearNoise');

Route::get('/phpinfo', 'IndexController@phpinfo');
