<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/22/13
 * Time: 4:40 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Karybu\Event;


use Symfony\Component\EventDispatcher\Event;

class ErrorEvent extends Event{
    private $level;
    private $message;
    private $file;
    private $line;
    private $context;

    function __construct($level, $message, $file, $line, $context=null)
    {
        $this->context = $context;
        $this->file = $file;
        $this->level = $level;
        $this->line = $line;
        $this->message = $message;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function toArray()
    {
        $arr = array();
        $arr['message'] = $this->getMessage();
        $arr['level'] = $this->getLevel();
        $arr['file'] = $this->getFile();
        $arr['line'] = $this->getLine();
        $arr['params'] = array();
        $context = $this->getContext();
        foreach ($context as $key => $value) {
            $arr['params'][$key] = (is_string($value) ? htmlspecialchars($value) : '[Object]');
        }
        return $arr;
    }

}