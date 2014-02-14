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
		$user = User::take(1)->orderBy('created_at', 'desc')->find(Session::get('id'));
		
		$this->publishMessage($this->room_id, array('user' => '', 'message' => $user->name.' has joined the room'));
		return $this->generateRoom();
	}
	
	public function listRooms()
	{
		$rooms = Room::take(50)->get()->lists('name','id');
		return Response::json($rooms, 200);
	}
    
    public function roomExists()
    {
        $room = Room::where('name', $this->room_name)->get()->first();
		if(!$room){ return false; }
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
	
	public function publishMessage($room, $data)
	{
		$amqp_host = Config::get('custom.amqp_host');
		$amqp_port = Config::get('custom.amqp_port');
		$amqp_user = Config::get('custom.amqp_user');
		$amqp_pass = Config::get('custom.amqp_pass');
		$amqp_vhost = Config::get('custom.amqp_vhost');
		
		$connection = new AMQPConnection($amqp_host, $amqp_port, $amqp_user, $amqp_pass, $amqp_vhost);
        $channel = $connection->channel();
		
		// Create and publish the message to the exchange.
		$data = array(
			'type' => $room,
			'data' => $data
		);
		$message = new AMQPMessage(json_encode($data), array('content_type' => 'text/plain'));
		$channel->basic_publish($message, 'updates');
		
		// Close connection.
		$channel->close();
		$connection->close();
	}
	
	public function presence()
	{
		$presence = Presence::take(1)->orderBy('created_at', 'desc')->get()[0];
		if(!$presence){ $this->respond->fail()->message('No presence found')->json(); }
		
		// Filter by room is value is passed
		$room_name = Input::get('room_name');
		if($room_name)
		{
			$room = Room::where('name', $room_name)->first();
			if(!$room){ return $this->respond->fail()->json(); }
			
			$rooms_presence = json_decode($presence->rooms, true);
			$rooms = $rooms_presence[$room->id];
			return Response::json(array('status' => 'ok', 'room' => $room->id, 'users' => $rooms), 200);
		}
		else
		{
			return Response::json(array('status' => 'ok', 'users' => $presence->users, 'rooms' => $presence->rooms), 200);
		}
	}
	
	public function updatePresence()
	{
		$rooms = Input::get('rooms');
		$users = Input::get('users');
		
		if(!$rooms or !$users){ return $this->respond->fail()->json(); }
		
		$presence = new Presence;
		$presence->users = $users;
		$presence->rooms = $rooms;
		$presence->save();
		
		$this->publishMessage('presence', array('users' => $users, 'rooms' => $rooms));
		
		return $this->respond->success()->json();
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
		
		$user = User::find(Session::get('id'));
		
		$this->publishMessage($room_id, array('user' => $user->name, 'message' => $message));
		
		return $this->respond->success()->json();
	}
    
    public function setRoom($str)
    {
        $this->room_name = $str;
    }

    
}