<?php

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


Route::get('/dbx/webhook', array(
	'uses' => 'DropboxController@challengeHandler'
));

Route::post('/dbx/webhook', array(
	'uses' => 'DropboxController@webhookHandler'
));

Route::get('/', array(
	'uses' => 'ContentController@serveDefault'
));

Route::get('/{file}', array(
	'uses' => 'ContentController@serve'
));