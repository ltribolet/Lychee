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

Route::get('install/', 'WelcomeController@view')->name('install-welcome');
Route::get('install/req', 'RequirementsController@view')->name('install-req');
Route::get('install/perm', 'PermissionsController@view')->name('install-perm');
Route::match(['get', 'post'], 'install/env', 'EnvController@view')->name('install-env');
Route::get('install/migrate', 'MigrationController@view')->name('install-migrate');
