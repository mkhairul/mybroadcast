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
    
    public function message($msg)
    {
        if(array_key_exists('message', $this->response))
        {
            $tmp = $this->response['message'];
            $messages = array();
            $messages[] = $tmp;
            $messages[] = $msg;
            $this->response['message'] = $messages;
        }
        else
        {
            $this->response['message'] = $messages;
        }
        return $this;
    }
    
    public function json()
    {
        return json_encode($this->response);
    }
    
}