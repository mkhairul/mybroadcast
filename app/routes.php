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
Route::get('/joinRoom', 'RoomController@joinRoom');
Route::post('/identify', 'UserController@identify');
Route::get('/identify', 'UserController@identify');
Route::post('/sendMessage', 'RoomController@sendMessage');
Route::get('/sendMessage', 'RoomController@sendMessage');
Route::get('/listRooms', 'RoomController@listRooms');
Route::post('/post', array(
                            'as' => 'post', 
                            'uses' => 'RoomController@postMessage'
                          ));

Route::post('/presence', 'RoomController@updatePresence');
Route::get('/presence', 'RoomController@presence');
Route::get('/history', 'RoomController@getHistory');

Route::get('/service/message', 'ServiceController@message');
Route::get('/service/joinRoom', 'ServiceController@joinRoom');
Route::post('/service/joinRoom', 'ServiceController@joinRoom');