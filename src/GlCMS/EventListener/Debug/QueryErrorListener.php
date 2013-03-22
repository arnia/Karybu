<?php

namespace GlCMS\EventListener\Debug;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use GlCMS\Event\DBEvents;
use GlCMS\Event\QueryEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class QueryErrorListener
 *
 * Logs query errors for easier debugging
 * Provides info about the module and act that caused the problem,
 *  error message and number
 *
 * @package GlCMS\EventListener\Debug
 */
class QueryErrorListener implements EventSubscriberInterface
{

    /** @var LoggerInterface */
    private $logger;

    /** @var array */
    private $failed_queries = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            DBEvents::QUERY_ENDED => array(array('logQueryIfItFailed', 34))
        );
    }

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @param QueryEvent $event
     */
    public function logQueryIfItFailed(QueryEvent $event)
    {
        if ($event->getResult() == 'Failed'){
            $this->logger->debug("Query failed:", $event->toArray());
            $this->failed_queries[] = $event;
        }
    }

    /**
     * Returns a list of information about queries that failed
     */
    public function getFailedQueries()
    {
        return $this->failed_queries;
    }
}