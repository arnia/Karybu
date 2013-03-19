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
    private $stopWatch;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     */
    public static function getSubscribedEvents()
    {
        return array(
            DBEvents::QUERY_STARTED => array(
                array('queryStarted', 34),
            ),
            DBEvents::QUERY_ENDED => array(
                array('queryEnded', 34),
            )
        );
    }

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
        $this->stopWatch = new Stopwatch();
    }

    public function queryStarted(QueryEvent $event)
    {
        $this->logQueryName($event);
        $this->stopWatch->start("query");
    }

    public function queryEnded(QueryEvent $event)
    {
        $swEvent = $this->stopWatch->stop("query");
        $this->logSummary($event, $swEvent);
    }

    // ************* PRIVATE AREA *****************

    private function logQueryName(QueryEvent $event) {
        $this->logger->debug("Query executed:", array($event->getQuery()));
    }

    private function logSummary(QueryEvent $event, StopwatchEvent $swEvent)
    {
        $periods = $swEvent->getPeriods();
        $durationInSec = $periods[0]->getDuration()/1000;

        if ($event->getResult() == 'Failed'){
            if(__DEBUG_DB_OUTPUT__ == 1)  {
                $debug_file = _XE_PATH_."files/_debug_db_query.new.php";
                $buff = array();
                if(!file_exists($debug_file)) $buff[] = '<?php exit(); ?>';
                $buff[] = print_r($this->getSummary($event, $swEvent), TRUE);

                if(@!$fp = fopen($debug_file, "a")) return;
                fwrite($fp, implode("\n", $buff)."\n\n");
                fclose($fp);
            }
        }

        // this may disappear from here
        $GLOBALS['__db_queries__'][] = $event->getQuery();
        $GLOBALS['__db_elapsed_time__'] += $durationInSec;

        // if __LOG_SLOW_QUERY__ if defined, check elapsed time and leave query log
        if(__LOG_SLOW_QUERY__ > 0 && $durationInSec > __LOG_SLOW_QUERY__) {
            $buff = '';
            $log_file = _XE_PATH_.'files/_db_slow_query.new.php';
            if(!file_exists($log_file)) {
                $buff = '<?php exit();?>'."\n";
            }

            $buff .= sprintf("%s\t%s\n\t%0.6f sec\tquery_id:%s\n\n",
                date("Y-m-d H:i"), $event->getQuery(),
                $durationInSec, $event->getQueryId());

            if($fp = fopen($log_file, 'a')) {
                fwrite($fp, $buff);
                fclose($fp);
            }
        }
    }

    private function getSummary(QueryEvent $event, StopwatchEvent $swEvent){
        $result = new \stdClass();
        $result->query = $event->getQuery();
        $result->elapsed_time = $swEvent->getDuration();
        $result->connection = $event->getConnection();
        $result->module = $event->getModule();
        $result->act = $event->getAct();
        $result->query_id = $event->getQueryId();
        $result->time = $event->getTime();
        $result->result = $event->getResult();
        $result->errno = $event->getErrno();
        $result->errstr = $event->getErrstr();
        return $result;
    }


}