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
		$room_exists = Room::where('name', $this->room_name)->get()->first();
		if(!$room_exists)
		{
			$room = new Room;
			$this->room_id = $room->id = uniqid();
			$room->name = $this->room_name;
			$room->save();
		}
		else
		{
			$room = $room_exists;
			$this->room_id = $room->id;
		}
		
		$this->publishMessage('rooms', array('rooms' => $this->listRooms(0)));
	}
    
    public function generateRoom()
    {
		return View::make('chat', array('room_id' => $this->room_id));
    }
	
	public function getHistory()
	{
		$room_name = Input::get('room_name');
		$room_id = Input::get('room_id');
		if($room_name)
		{
			$room = Room::where('name', $room_name)->get()->first();
		}
		else
		{
			$room = Room::where('id', $room_id)->get()->first();
		}
		if(!$room){ return $this->respond->fail()->json(); }
		
		$total_rows = Chat::where('room_id', $room->id)->count();
		$chat_log = Chat::take(50)
					->leftJoin('users', 'users.id', '=', 'chat.user_id')
					->where('room_id', $room->id)
					->orderBy('chat.created_at', 'desc')
					->select('users.name', 'chat.message', 'chat.created_at')->get();
		$response = array();
		foreach($chat_log as $chat)
		{
			$response[] = array('user' => $chat->name, 'message' => $chat->message, 'created_at' => $chat->created_at);
		}
		return Response::json(array('message' => $response, 'total' => $total_rows), 200);
	}
	
	public function getMessage()
	{
		$user = User::find(Session::get('id'));
		if($user)
		{
			$user->first();
			$chat = Chat::leftJoin('users', 'users.id', '=', 'chat.user_id')
					->where('topics', 'LIKE', '%'.$user->name.'%')
					->select('users.name', 'chat.message', 'chat.created_at', 'chat.topics')
					->get();
		}
		else
		{
			$chat = array();
		}
		return Response::json($chat, 200);
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
		
		$this->publishMessage($this->room_id,
							  array('user' => '', 'message' => $user->name.' has joined the room'));
		
		$chat = new Chat;
		$chat->message = $user->name.' has joined the room';
		$chat->room_id = $this->room_id;
		$chat->save();
		
		return $this->generateRoom();
	}
	
	public function listRooms($json=1)
	{
		$rooms = Room::take(50)->get()->lists('name','id');
		if($json)
		{
			return Response::json($rooms, 200);
		}
		{
			return $rooms;
		}
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
	
	public function postMessage()
	{
		$message = Input::get('message');
		$topics = Input::get('topics');
		if(!$message){ return $this->respond->fail()->json(); }
		
		if($topics === ""){ $topics = array(); }
		
		// Get all the hashtags in the message
		preg_match_all("/\B#\w*[a-zA-Z]+\w*/", $message, $matches);
		if(count($matches[0]) > 0)
		{
			foreach($matches[0] as $topic)
			{
				$topic = str_replace('#', '', $topic);
				array_push($topics, $topic);
			}
		}
		
		// For each topics, create a room
		foreach($topics as $topic)
		{
			$this->room_name = $topic;
			$this->createRoom();
		}
		
		$chat = new Chat;
		$chat->user_id = Session::get('id');
		$chat->message = $message;
		$chat->topics = json_encode($topics);
		$chat->save();
		
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