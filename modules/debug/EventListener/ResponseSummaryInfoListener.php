<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/20/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Karybu\Module\Debug\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Karybu\Event\DBEvents;
use Karybu\Event\QueryEvent;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Psr\Log\LoggerInterface;
use \FirePHP;
use \Context;

/**
 * Class ResponseSummaryInfoListener
 *
 * Logs aggregate info regarding duration of QUB's main events
 *  - template compiling
 *  - xml query loading and execution
 *  - widget compiling
 * and others.
 *
 * Also provides generic request and reponse info.
 *
 * // TODO Finish cleaning up this class
 *
 * @package Karybu\Module\Debug\EventListener
 */
class ResponseSummaryInfoListener  implements EventSubscriberInterface {

    /** @var LoggerInterface */
    private $logger;

    /** @var Stopwatch */
    private $queryStopwatch;

    /**
     * @var int Sum of the duration of all calls to execute_query
     *
     * Includes xml file compilation, loading of the parsed php file and all
     * legacy: __dbclass_elapsed_time__
     * */
    private $total_execute_query_duration = 0;

    private $responseLength = 0;

    public static function getSubscribedEvents()
    {
        return array(
            DBEvents::EXECUTE_QUERY_STARTED => array(array('executeQueryStarted', 34)),
            DBEvents::EXECUTE_QUERY_ENDED => array(array('executeQueryEnded', 34)),
            KernelEvents::TERMINATE => array(
                array('doTriggerResponseStatistics', 0)
            ));
    }

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
        $this->queryStopwatch = new Stopwatch();
    }

    public function executeQueryStarted(QueryEvent $event)
    {
        $this->queryStopwatch->start("executeQuery");
    }

    public function executeQueryEnded(QueryEvent $event)
    {
        $swEvent = $this->queryStopwatch->stop("executeQuery");
        $this->total_execute_query_duration += $swEvent->getDuration();
    }

    public function doTriggerResponseStatistics(PostResponseEvent $event){
        $this->_debugOutput($this->getContentLength($event));
    }

    // ************** PRIVATE AREA ****************

    private function getContentLength(PostResponseEvent $event){
        if ($event == null)
            return 0;
        $contentLength = $event->getResponse()->headers->get('Content-Length');
        if (is_array($contentLength))
            $contentLength = $contentLength[0];
        if (isset($contentLength) && is_numeric($contentLength)) {
            return $contentLength;
        } else{
            $contentLength = strlen($event->getResponse()->getContent());
            $event->getResponse()->headers->set('Content-Length', $contentLength);
        }
        return $contentLength;
    }

    /**
     * Moved from DisplayHandler
     * @param $contentLength
     * @return string
     */
    private function _debugOutput($contentLength) {
        $buff = '';
        $end = getMicroTime();

        // display total execution time and Request/Response info
        if(__DEBUG_PROTECT__ == 1 && __DEBUG_PROTECT_IP__ != $_SERVER['REMOTE_ADDR']) {
            return;
        }

        // Request/Response information
        $buff .= "\n- Request/ Response info\n";
        $buff .= sprintf("\tRequest URI \t\t\t: %s:%s%s%s%s\n", $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']?'?':'', $_SERVER['QUERY_STRING']);
        $buff .= sprintf("\tRequest method \t\t\t: %s\n", $_SERVER['REQUEST_METHOD']);
        $buff .= sprintf("\tResponse method \t\t: %s\n", Context::getResponseMethod());
        $buff .= sprintf("\tResponse contents size\t\t: %d byte\n", $contentLength);
        // total execution time
        $buff .= sprintf("\n- Total elapsed time : %0.5f sec\n", $end-__StartTime__);

        $buff .= sprintf("\tclass file load elapsed time \t: %0.5f sec\n", $GLOBALS['__elapsed_class_load__']);
        $buff .= sprintf("\tTemplate compile elapsed time\t: %0.5f sec (%d called)\n", $GLOBALS['__template_elapsed__'], $GLOBALS['__TemplateHandlerCalled__']);
        $buff .= sprintf("\tXmlParse compile elapsed time\t: %0.5f sec\n", $GLOBALS['__xmlparse_elapsed__']);
        $buff .= sprintf("\tPHP elapsed time \t\t: %0.5f sec\n", $end-__StartTime__-$GLOBALS['__template_elapsed__']-$GLOBALS['__xmlparse_elapsed__']-$GLOBALS['__db_elapsed_time__']-$GLOBALS['__elapsed_class_load__']);
        $buff .= sprintf("\tDB class elapsed time \t\t: %0.3f sec\n", $GLOBALS['__dbclass_elapsed_time__'] -$GLOBALS['__db_elapsed_time__']);
        // widget execution time
        $buff .= sprintf("\n\tWidgets elapsed time \t\t: %0.5f sec", $GLOBALS['__widget_excute_elapsed__']);
        // layout execution time
        $buff .= sprintf("\n\tLayout compile elapsed time \t: %0.5f sec", $GLOBALS['__layout_compile_elapsed__']);
        // Widgets, the editor component replacement time
        $buff .= sprintf("\n\tTrans Content \t\t\t: %0.5f sec\n", $GLOBALS['__trans_content_elapsed__']);

        $this->logger->debug($buff);
    }
}