<?php

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ServiceController extends Controller {

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

	public function index()
	{
            if(!Input::has('param'))
            {
                return 'No input defined';
            }
            $param = json_decode(Input::get('param'));
            
            // Check if a module has been defined
            if(!array_key_exists($param, 'module')){ return 'No module defined'; }
            $this->$param['module']['name']($param['module']['param']);
	}
        
        public function message($param='')
        {
            $connection = new AMQPConnection('175.41.180.181', 5672, 'guest', 'guest');
            $channel = $connection->channel();

            // Create a fanout exchange.
            // A fanout exchange broadcasts to all known queues.
            $channel->exchange_declare('updates', 'fanout', false, false, false);
            
            // Create and publish the message to the exchange.
            $data = array(
                'type' => 'update',
                'data' => array(
                    'minutes' => rand(0, 60),
                    'seconds' => rand(0, 60)
                )
            );
            $message = new AMQPMessage(json_encode($data));
            $channel->basic_publish($message, 'updates');
            
            // Close connection.
            $channel->close();
            $connection->close();
            return json_encode(array('status' => 'ok'));
        }
        
        public function joinRoom()
        {
            return Response::json(array('status' => 'ok'), 200);
        }
	
}