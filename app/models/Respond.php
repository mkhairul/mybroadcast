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
    
    public function json()
    {
        return json_encode($this->response);
    }
    
}