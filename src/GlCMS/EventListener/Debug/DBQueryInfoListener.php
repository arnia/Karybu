<?php

namespace GlCMS\EventListener\Debug;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use GlCMS\Event\DBEvents;
use GlCMS\Event\QueryEvent;

class DBQueryInfoListener implements EventSubscriberInterface {

    /** @var LoggerInterface */
    private $logger;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     */
    public static function getSubscribedEvents()
    {
        return array(
            DBEvents::QUERY_STARTED => array(
                array('logQueryName', 34),
            )
        );
    }

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function logQueryName(QueryEvent $event) {
        $this->logger->debug("Query executed:", array($event->getQuery()));
    }


}