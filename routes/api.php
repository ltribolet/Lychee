<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/users', 'UserController@index')->middleware('upload')->name('users.index');

Route::get('/session', 'SessionController@init');
Route::post('/session', 'SessionController@login');
Route::delete('/session', 'SessionController@logout');

Route::get('/albums', 'AlbumsController@index');
Route::get('/albums/position-data', 'AlbumsController@getPositionData');

Route::middleware('read')->group(function (): void {
    Route::get('/album/archive', 'AlbumController@export');
    Route::get('/album/{albumId}', 'AlbumController@show');
    Route::post('/album/{albumId}/position-data', 'AlbumController@showPositionData');
});

/*
 * Legacy APIs
 */

Route::post('/Session::init', 'SessionController@init');
Route::post('/Session::login', 'SessionController@login');
Route::post('/Session::logout', 'SessionController@logout');

Route::post('/Albums::get', 'AlbumsController@get');
Route::post('/Albums::getPositionData', 'AlbumsController@getPositionData');

Route::post('/Album::get', 'AlbumController@get')->middleware('read');
Route::post('/Album::getPositionData', 'AlbumController@getPositionData')->middleware('read');
Route::post('/Album::getPublic', 'AlbumController@getPublic');
Route::post('/Album::add', 'AlbumController@add')->middleware('upload');
Route::post('/Album::setTitle', 'AlbumController@setTitle')->middleware('upload');
Route::post('/Album::setDescription', 'AlbumController@setDescription')->middleware('upload');
Route::post('/Album::setPublic', 'AlbumController@setPublic')->middleware('upload');
Route::post('/Album::delete', 'AlbumController@delete')->middleware('upload');
Route::post('/Album::merge', 'AlbumController@merge')->middleware('upload');
Route::post('/Album::move', 'AlbumController@move')->middleware('upload');
Route::post('/Album::setLicense', 'AlbumController@setLicense')->middleware('upload');
Route::get('/Album::getArchive', 'AlbumController@export')->middleware('read');

Route::post('/Frame::getSettings', 'FrameController@getSettings');

Route::post('/Photo::get', 'PhotoController@get')->middleware('read');
Route::post('/Photo::getRandom', 'PhotoController@getRandom');
Route::post('/Photo::setTitle', 'PhotoController@setTitle')->middleware('upload');
Route::post('/Photo::setDescription', 'PhotoController@setDescription')->middleware('upload');
Route::post('/Photo::setStar', 'PhotoController@setStar')->middleware('upload');
Route::post('/Photo::setPublic', 'PhotoController@setPublic')->middleware('upload');
Route::post('/Photo::setAlbum', 'PhotoController@setAlbum')->middleware('upload');
Route::post('/Photo::setTags', 'PhotoController@setTags')->middleware('upload');
Route::post('/Photo::add', 'PhotoController@add')->middleware('upload');
Route::post('/Photo::delete', 'PhotoController@delete')->middleware('upload');
Route::post('/Photo::duplicate', 'PhotoController@duplicate')->middleware('upload');
Route::post('/Photo::setLicense', 'PhotoController@setLicense')->middleware('upload');
Route::get('/Photo::getArchive', 'PhotoController@getArchive')->middleware('read');
Route::get('/Photo::clearSymLink', 'PhotoController@clearSymLink')->middleware('admin');

Route::post('/PhotoEditor::rotate', 'PhotoEditorController@rotate')->middleware('upload');

Route::post('/Sharing::List', 'SharingController@listSharing')->middleware('upload');
Route::post('/Sharing::ListUser', 'SharingController@getUserList')->middleware('upload');
Route::post('/Sharing::Add', 'SharingController@add')->middleware('upload');
Route::post('/Sharing::Delete', 'SharingController@delete')->middleware('upload');

Route::post('/Settings::setLogin', 'SettingsController@setLogin');

Route::post('/Import::url', 'ImportController@url')->middleware('upload');

Route::post('/User::List', 'UserController@list')->middleware('upload');

Route::post('/Diagnostics', 'DiagnosticsController@get');
Route::post('/Diagnostics::getSize', 'DiagnosticsController@get_size');

Route::post('/search', 'SearchController@search');
