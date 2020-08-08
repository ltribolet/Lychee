<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::feeds();

Route::get('/', 'IndexController@show')->name('home')->middleware('install');
Route::get('/gallery', 'IndexController@gallery')->name('gallery')->middleware('install');

/*
 * TODO see to add better redirection functionality later.
 * This is to prevent instagram from taking control our # in url when sharing an album
 * and not consider it as an hash-tag.
 *
 * Other ideas, redirection by album name, photo title...
 */
Route::get('/r/{albumid}/{photoid}', 'RedirectController@photo');
Route::get('/r/{albumid}', 'RedirectController@album');

Route::get('/view', 'ViewController@view');
Route::get('/demo', 'DemoController@js');
Route::get('/frame', 'FrameController@init')->name('frame');
Route::get('/Diagnostics', 'DiagnosticsController@show');

// entry point if options are not initialized
Route::post('/php/index.php', 'SessionController@init');

// This route NEEDS to be the last one as it will catch anything else.
Route::get('/{page}', 'PageController@page');
