<?php

namespace Karybu\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

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
    private $elapsed_time;

    private $queryName, $sql;

    /** @var Stopwatch */
    private $stopwatch;

    public function __construct($sql_query_text = null)
    {
        $this->setQuery($sql_query_text);
        $this->stopwatch = new Stopwatch();
    }

    public function startTiming()
    {
        $this->time = time();
        $this->stopwatch->start("query");
    }

    public function stopTiming()
    {
        $duration = $this->stopwatch->stop("query");
        $this->elapsed_time = $duration->getDuration();
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

    private function doRegexSplit()
    {
        if (preg_match('/\s*\/\*\s*(.*)\s*\*\/\s*/U', $this->getQuery(), $matches)) {
            $this->queryName = trim($matches[1]);
            $this->sql = trim(str_replace($matches[0], '', $this->getQuery()));
        }
        else $this->sql = $this->getQuery();
    }

    public function getSql()
    {
        if (!$this->sql) $this->doRegexSplit();
        return $this->sql;
    }

    public function getQueryName() {
        if (!$this->queryName) $this->doRegexSplit();
        return $this->queryName;
    }

    public function getElapsedTime() {
        return $this->elapsed_time;
    }

    public function toArray()
    {
        if($this->getResult() == 'Failed') {
            return array(
                "query_id" => $this->getQueryId(),
                "sql_text" => $this->getQuery() . PHP_EOL,
                "error_no" => $this->getErrno(),
                "error_message" => $this->getErrstr() . PHP_EOL,
                "module" => $this->getModule(),
                "act" => $this->getAct(),
                "connection" => $this->getConnection() . PHP_EOL,
                "time" => $this->getTime(),
                "elapsed_time" => $this->getElapsedTime()
            );
        }

        return array(
            $this->getQueryId(),
            $this->getQuery(),
            $this->getConnection(),
            $this->getTime(),
            $this->getElapsedTime()
        );
    }

    public function __toString()
    {
        return sprintf("[%0.5f] %s ",
            $this->elapsed_time,
            $this->query
        );
    }
}