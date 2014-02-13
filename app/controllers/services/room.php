<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RoomController extends BaseController {
    
    var $room_name;
	var $room_id;
	
	public function __construct()
	{
		$this->respond = new Respond;
	}
	
	public function createRoom()
	{
		$room = new Room;
		$this->room_id = $room->id = uniqid();
		$room->name = $this->room_name;
		$room->save();
	}
    
    public function generateRoom()
    {
		return View::make('chat', array('room_id' => $this->room_id));
    }
    
    public function joinRoom()
	{
        $room_name = Input::get('room_name');
        if($room_name){ $this->setRoom($room_name); }
        
        // Check if room exists
        if(!$this->roomExists())
        {
            $this->createRoom();
        }
		return $this->generateRoom();
	}
    
    public function roomExists()
    {
        $room = Room::where('name', $this->room_name)->get()->first();
		if($room->count() > 0)
		{
			$this->room_id = $room->id;
			return true;
		}
		else
		{
			return false;
		}
    }
	
	public function sendMessage()
	{
		$message = Input::get('message');
		$room_id = Input::get('id');
		if(!$message){ return $this->respond->fail()->json(); }
		
		$chat = new Chat;
		$chat->user_id = Session::get('id');
		$chat->message = $message;
		$chat->room_id = $room_id;
		$chat->save();
		
		$connection = new AMQPConnection('175.41.180.181', 5672, 'guest', 'guest', '/');
        $channel = $connection->channel();
		// $channel->exchange_declare('updates', 'fanout', false, false, false);
		// Create and publish the message to the exchange.
		$data = array(
			//'type' => 'update',
			'type' => $room_id,
			'data' => array(
				'user' => Session::get('id'),
				'message' => $message
			)
		);
		$message = new AMQPMessage(json_encode($data), array('content_type' => 'text/plain'));
		$channel->basic_publish($message, 'updates');
		
		// Close connection.
		$channel->close();
		$connection->close();
		
		return $this->respond->success()->json();
	}
    
    public function setRoom($str)
    {
        $this->room_name = $str;
    }

    
}