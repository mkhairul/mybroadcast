<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function showWelcome()
	{
		return View::make('main');
	}
	
	public function broadcast()
	{
		return View::make('broadcast_start');
	}
	
	public function joinRoom()
	{
		$room_id = uniqid();
		return View::make('chat', array('room_id' => $room_id));
	}

}