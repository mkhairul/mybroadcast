<?php
class UserController extends Controller {
    
    public function identify()
    {
        $name = Input::get('name');
        if(!$name){ $name = 'Anon_' . uniqid(); }
        
        if(!Session::get('id'))
        {
            $this->createUser($name);
        }
        else
        {
            $user = User::find(Session::get('id'));
            if(!$user)
            {
                $this->createUser($name);
            }
        }
        return json_encode(array('status' => 'success'));
    }
    
    public function setUser()
    {
        
    }
    
    public function createUser($username)
    {
        $user = new User;
        $user->name = $username;
        $user->save();
        
        $this->user_id = $user->id;
        Session::put('id', $this->user_id);
    }
    
}