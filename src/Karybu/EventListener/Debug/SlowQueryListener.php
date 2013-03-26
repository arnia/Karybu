<?php

namespace Karybu\EventListener\Debug;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Karybu\Event\DBEvents;
use Karybu\Event\QueryEvent;

/**
 * Class SlowQueryListener
 *
 * Checks each query's duration and logs any query that takes longer than a minimum duration
 * given in constructor
 *
 * @package Karybu\EventListener\Debug
 */
class SlowQueryListener implements EventSubscriberInterface
{

    /** @var LoggerInterface */
    private $logger;
    /** @var int Query duration threshold */
    private $min_duration;

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
            DBEvents::QUERY_ENDED => array(array('logQueryIfSlow', 34))
        );
    }

    /**
     * @param int $min_duration
     * @param LoggerInterface $logger
     */
    public function __construct($min_duration, LoggerInterface $logger) {
        $this->logger = $logger;
        $this->min_duration = $min_duration;
    }

    /**
     * Stops stopwatch and computes query durations; logs query info if it took too long
     *
     * @param QueryEvent $event
     */
    public function logQueryIfSlow(QueryEvent $event)
    {
        if($event->getElapsedTime() > $this->min_duration) {
            $this->logger->debug("Slow query", array($event->getQuery()));
        }
    }




}