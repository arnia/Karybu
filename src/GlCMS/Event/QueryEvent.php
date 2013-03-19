<?php

namespace GlCMS\Event;

use Symfony\Component\EventDispatcher\Event;

class QueryEvent extends Event {

    private $query;
    private $connection;
    private $query_id;
    private $module;
    private $act;
    private $time;
    private $result;
    private $errno;
    private $errstr;

    public function __construct()
    {
        $this->time = date('Y-m-d H:i:s');
    }

    public function setAct($act)
    {
        $this->act = $act;
    }

    public function getAct()
    {
        return $this->act;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function setErrno($errno)
    {
        $this->errno = $errno;
    }

    public function getErrno()
    {
        return $this->errno;
    }

    public function setErrstr($errstr)
    {
        $this->errstr = $errstr;
    }

    public function getErrstr()
    {
        return $this->errstr;
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setQueryId($query_id)
    {
        $this->query_id = $query_id;
    }

    public function getQueryId()
    {
        return $this->query_id;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setQuery($query) {
        $this->query = $query;
    }

    public function getQuery() {
        return $this->query;
    }
}