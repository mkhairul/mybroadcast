<?php
class RoomController extends Controller {
    
    var $room_name;
    
    public function generateRoom()
    {
        $room_id = uniqid();
		return View::make('chat', array('room_id' => $room_id));
    }
    
    public function joinRoom()
	{
        $room_name = Input::get('room_name');
        if($room_name){ $this->setRoom($room_name); }
        
        // Check if room exists
        if($this->roomExists())
        {
            
        }
		$this->generateRoom();
	}
    
    public function roomExists()
    {
        
    }
    
    public function setRoom($str)
    {
        $this->room_name = $str;
    }

    
}