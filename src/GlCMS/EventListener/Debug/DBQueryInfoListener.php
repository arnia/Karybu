<?php

namespace GlCMS\EventListener\Debug;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use GlCMS\Event\DBEvents;
use GlCMS\Event\QueryEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class DBQueryInfoListener
 *
 * Gathers information about all the queries executed in a request
 * Calculates total query execution duration
 *
 * @package GlCMS\EventListener\Debug
 */
class DBQueryInfoListener implements EventSubscriberInterface {

    /** @var LoggerInterface */
    private $logger;

    /** @var QueryEvent[] List of all queries executed in a request; legacy: __db_queries__ */
    private $queries_executed = array();

    /** @var int Sum of all queries' duration; legacy: __db_elapsed_time__ */
    private $total_query_duration = 0;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     */
    public static function getSubscribedEvents()
    {
        return array(
            DBEvents::QUERY_ENDED => array(array('logQueryAndAddDurationToTotal', 34)),
            KernelEvents::TERMINATE => array(array('logTotalQueryDuration'))
        );
    }

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * After each query, log it and add elapsed time to total
     *
     * @param QueryEvent $event
     */
    public function logQueryAndAddDurationToTotal(QueryEvent $event)
    {
        $this->logger->debug("Query executed:", array((string)$event));
        $this->queries_executed[] = $event;
        $this->total_query_duration += $event->getElapsedTime();
    }

    /**
     * At the very end of the script, log the aggregate query information
     *
     * @param PostResponseEvent $event
     */
    public function logTotalQueryDuration(PostResponseEvent $event)
    {
        $this->logger->debug(
            sprintf("\n- DB Queries : %d Queries. %0.3f sec\n",
                count($this->queries_executed),
                $this->total_query_duration)
        );
    }

    /**
     * Returns a list of all queries executed, as QueryEvent objects
     *
     * @return array|\GlCMS\Event\QueryEvent[]
     */
    public function getQueries()
    {
        return $this->queries_executed;
    }

    /**
     * Total execution time of all queries
     *
     * @return int
     */
    public function getTotalQueryDuration()
    {
        return $this->total_query_duration;
    }
}