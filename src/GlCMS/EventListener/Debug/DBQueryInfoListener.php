<?php

namespace GlCMS\EventListener\Debug;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use GlCMS\Event\DBEvents;
use GlCMS\Event\QueryEvent;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class DBQueryInfoListener implements EventSubscriberInterface {

    /** @var LoggerInterface */
    private $logger;

    /** @var Stopwatch */
    private $queryStopwatch;


    /** @var QueryEvent[] List of all queries executed in a request; legacy: __db_queries__ */
    private $queries_executed = array();

    /** @var int Sum of all queries' duration; legacy: __db_elapsed_time__ */
    private $total_query_duration = 0;

    /**
     * @var int Sum of the duration of all calls to execute_query
     *
     * Includes xml file compilation, loading of the parsed php file and all
     * legacy: __dbclass_elapsed_time__
     * */
    private $total_execute_query_duration = 0;


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     */
    public static function getSubscribedEvents()
    {
        return array(
            DBEvents::QUERY_ENDED => array(array('queryEnded', 34)),
            DBEvents::EXECUTE_QUERY_STARTED => array(array('executeQueryStarted', 34)),
            DBEvents::EXECUTE_QUERY_ENDED => array(array('executeQueryEnded', 34))
        );
    }

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
        $this->queryStopwatch = new Stopwatch();
    }

    public function queryEnded(QueryEvent $event)
    {
        $this->logger->debug("Query executed:", array((string)$event));
        $this->queries_executed[] = $event;
        $this->total_query_duration += $event->getElapsedTime();
    }

    public function executeQueryStarted(QueryEvent $event)
    {
        $this->queryStopwatch->start("executeQuery");
    }

    public function executeQueryEnded(QueryEvent $event)
    {
        $swEvent = $this->queryStopwatch->stop("executeQuery");
        $this->total_execute_query_duration += $this->durationInSec($swEvent);
    }

    /**
     * Gets a StopwatchEvent duration in seconds
     *
     * @param StopwatchEvent $swEvent
     * @return int
     */
    private function durationInSec(StopwatchEvent $swEvent){
        $periods = $swEvent->getPeriods();
        return end($periods)->getDuration()/1000;
    }

}