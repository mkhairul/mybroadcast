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

Route::match(array('GET', 'POST'), '/identify', 'UserController@identify');
Route::match(array('GET', 'POST'), '/sendMessage', 'RoomController@sendMessage');
Route::get('/listRooms', 'RoomController@listRooms');
Route::post('/post', array(
                            'as' => 'post', 
                            'uses' => 'RoomController@postMessage'
                          ));
Route::match(array('GET', 'POST'), '/presence', 'RoomController@updatePresence');
Route::get('/history', 'RoomController@getHistory');
Route::get('/getMessage', array(
                            'as' => 'getMessage', 
                            'uses' => 'RoomController@getMessage'
                          ));

Route::get('/service/message', 'ServiceController@message');
Route::get('/service/joinRoom', 'ServiceController@joinRoom');
Route::post('/service/joinRoom', 'ServiceController@joinRoom');