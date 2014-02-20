<?php

class Respond{
    
    var $response = array();
    
    public function fail()
    {
        $this->response['status'] = 'error';
        return $this;
    }
    
    public function success()
    {
        $this->response['status'] = 'ok';
        return $this;
    }
    
    public function message($msg, $key_name="message")
    {
        if(array_key_exists($key_name, $this->response))
        {
            $tmp = $this->response[$key_name];
            $messages = array();
            $messages[] = $tmp;
            $messages[] = $msg;
            $this->response[$key_name] = $messages;
        }
        else
        {
            $this->response[$key_name][] = $msg;
        }
        return $this;
    }
    
    public function json()
    {
        return json_encode($this->response);
    }
    
}