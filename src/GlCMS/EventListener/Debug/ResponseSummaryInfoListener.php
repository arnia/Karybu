<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/20/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace GlCMS\EventListener\Debug;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use \FirePHP;
use \Context;


class ResponseSummaryInfoListener  implements EventSubscriberInterface {

    private $responseLength = 0;

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(
                array('doTriggerResponseStatistics', 0)
            ));
    }

    public function doTriggerResponseStatistics(FilterResponseEvent $event){
        $this->_debugOutput($this->getContentLength($event));
    }

    // ************** PRIVATE AREA ****************

    private function getContentLength(FilterResponseEvent $event){
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
        if(!__DEBUG__) return;
        $buff = '';
        $end = getMicroTime();
        // Firebug console output
        if(__DEBUG_OUTPUT__ == 2 && version_compare(PHP_VERSION, '6.0.0') === -1) {
            static $firephp;
            if(!isset($firephp)) $firephp = FirePHP::getInstance(true);

            if(__DEBUG_PROTECT__ == 1 && __DEBUG_PROTECT_IP__ != $_SERVER['REMOTE_ADDR']) {
                $firephp->fb('Change the value of __DEBUG_PROTECT_IP__ into your IP address in config/config.user.inc.php or config/config.inc.php', 'The IP address is not allowed.');
                return;
            }
            // display total execution time and Request/Response info
            if(__DEBUG__ & 2) {
                $firephp->fb(
                    array('Request / Response info >>> '.$_SERVER['REQUEST_METHOD'].' / '.Context::getResponseMethod(),
                        array(
                            array('Request URI', 'Request method', 'Response method', 'Response contents size'),
                            array(
                                sprintf("%s:%s%s%s%s", $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']?'?':'', $_SERVER['QUERY_STRING']),
                                $_SERVER['REQUEST_METHOD'],
                                Context::getResponseMethod(),
                                $contentLength.' byte'
                            )
                        )
                    ),
                    'TABLE'
                );
                $firephp->fb(
                    array('Elapsed time >>> Total : '.sprintf('%0.5f sec', $end - __StartTime__),
                        array(array('DB queries', 'class file load', 'Template compile', 'XmlParse compile', 'PHP', 'Widgets', 'Trans Content'),
                            array(
                                sprintf('%0.5f sec', $GLOBALS['__db_elapsed_time__']),
                                sprintf('%0.5f sec', $GLOBALS['__elapsed_class_load__']),
                                sprintf('%0.5f sec (%d called)', $GLOBALS['__template_elapsed__'], $GLOBALS['__TemplateHandlerCalled__']),
                                sprintf('%0.5f sec', $GLOBALS['__xmlparse_elapsed__']),
                                sprintf('%0.5f sec', $end-__StartTime__-$GLOBALS['__template_elapsed__']-$GLOBALS['__xmlparse_elapsed__']-$GLOBALS['__db_elapsed_time__']-$GLOBALS['__elapsed_class_load__']),
                                sprintf('%0.5f sec', $GLOBALS['__widget_excute_elapsed__']),
                                sprintf('%0.5f sec', $GLOBALS['__trans_content_elapsed__'])
                            )
                        )
                    ),
                    'TABLE'
                );
            }
            // display DB query history
            if((__DEBUG__ & 4) && $GLOBALS['__db_queries__']) {
                $queries_output = array(array('Query', 'Elapsed time', 'Result'));
                foreach($GLOBALS['__db_queries__'] as $query) {
                    array_push($queries_output, array($query['query'], sprintf('%0.5f', $query['elapsed_time']), $query['result']));
                }
                $firephp->fb(
                    array(
                        'DB Queries >>> '.count($GLOBALS['__db_queries__']).' Queries, '.sprintf('%0.5f sec', $GLOBALS['__db_elapsed_time__']),
                        $queries_output
                    ),
                    'TABLE'
                );
            }
            // dislpay the file and HTML comments
        } else {
            // display total execution time and Request/Response info
            if(__DEBUG__ & 2) {
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
            }
            // DB Logging
            if(__DEBUG__ & 4) {
                if(__DEBUG_PROTECT__ == 1 && __DEBUG_PROTECT_IP__ != $_SERVER['REMOTE_ADDR']) {
                    return;
                }

                if($GLOBALS['__db_queries__']) {
                    $buff .= sprintf("\n- DB Queries : %d Queries. %0.3f sec\n", count($GLOBALS['__db_queries__']), $GLOBALS['__db_elapsed_time__']);
                    $num = 0;

                    foreach($GLOBALS['__db_queries__'] as $query) {
                        $buff .= sprintf("\t%02d. %s\n\t\t%0.3f sec. ", ++$num, $query['query'], $query['elapsed_time']);
                        if($query['result'] == 'Success') {
                            $buff .= "Query Success\n";
                        } else {
                            $buff .= sprintf("Query $s : %d\n\t\t\t   %s\n", $query['result'], $query['errno'], $query['errstr']);
                        }
                        $buff .= sprintf("\t\tConnection: %s\n", $query['connection']);
                    }
                }
            }
            // Output in HTML comments
            if($buff && __DEBUG_OUTPUT__ == 1 && Context::getResponseMethod() == 'HTML') {
                $buff = sprintf("[%s %s:%d]\n%s\n", date('Y-m-d H:i:s'), $file_name, $line_num, print_r($buff, true));

                if(__DEBUG_PROTECT__ == 1 && __DEBUG_PROTECT_IP__ != $_SERVER['REMOTE_ADDR']) {
                    $buff = 'The IP address is not allowed. Change the value of __DEBUG_PROTECT_IP__ into your IP address in config/config.user.inc.php or config/config.inc.php';
                }

                return "<!--\r\n".$buff."\r\n-->";
            }
            // Output to a file
            if($buff && __DEBUG_OUTPUT__ == 0) {
                $debug_file = _XE_PATH_.'files/_debug_message.php';
                $buff = sprintf("[%s %s:%d]\n%s\n", date('Y-m-d H:i:s'), $file_name, $line_num, print_r($buff, true));

                $buff = str_repeat('=', 40)."\n".$buff.str_repeat('-', 40);
                $buff = "\n<?php\n/*".$buff."*/\n?>\n";

                if(@!$fp = fopen($debug_file, 'a')) return;
                fwrite($fp, $buff);
                fclose($fp);
            }
        }
    }

}