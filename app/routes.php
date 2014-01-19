<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/wut', function()
{
	//return View::make('hello');
	//return 'HomeController@showWelcome';
});

Route::get('/', 'HomeController@showWelcome');
Route::get('/start-broadcast', 'HomeController@broadcast');
Route::get('/service/message', 'ServiceController@message');