<?php

class Object
{
    public $error;
    public $message;

    public function __construct($error = 0, $message = 'success')
    {
        $this->error = $error;
        $this->message = $message;
    }

    public function toBool()
    {
        return $this->error==0?true:false;
    }
}