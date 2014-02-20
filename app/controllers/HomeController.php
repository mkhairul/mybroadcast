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
		$name = '';
		if(Session::get('id'))
		{
			$user = User::find(Session::get('id'));
			if($user)
			{
				$name = $user->name;
			}
			else
			{
				Session::forget('id');
			}
		}
		return View::make('main', array('name' => $name));
	}
	
	public function broadcast()
	{
		return View::make('broadcast_start');
	}

}