<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/14/13
 * Time: 7:48 PM
 * To change this template use File | Settings | File Templates.
 */

namespace GlCMS\Utils\Statistics;

use GlCMS\Utils\StopWatch\IStopWatch;
use GlCMS\Utils\StopWatch\IStopWatchListener;
use Psr\Log\LoggerInterface;

class DBQueriesStatistics implements IStopWatchListener{

    /** @var $logger LoggerInterface */
    private $logger = null;

    public function __construct(LoggerInterface $logger){
        $this->logger = $logger;
    }

    public function act($eventType, IStopWatch $stopWatch)
    {
        // if ($this->logger){
        //      $this->logger->debug(print_r($stopWatch->getCurrentSummary(), true));
        // }

        $currentSummary = $stopWatch->getCurrentSummary();
        if ($currentSummary['result'] == 'Failed'){
            if(__DEBUG_DB_OUTPUT__ == 1)  {
                $debug_file = _XE_PATH_."files/_debug_db_query.new.php";
                $buff = array();
                if(!file_exists($debug_file)) $buff[] = '<?php exit(); ?>';
                $buff[] = print_r($currentSummary, TRUE);

                if(@!$fp = fopen($debug_file, "a")) return;
                fwrite($fp, implode("\n", $buff)."\n\n");
                fclose($fp);
            }
        }

        // this may disappear from here
        $GLOBALS['__db_queries__'][] = $currentSummary;
        $GLOBALS['__db_elapsed_time__'] += $stopWatch->getLapTime();

        // if __LOG_SLOW_QUERY__ if defined, check elapsed time and leave query log
        if(__LOG_SLOW_QUERY__ > 0 && $stopWatch->getLapTime() > __LOG_SLOW_QUERY__) {
            $buff = '';
            $log_file = _XE_PATH_.'files/_db_slow_query.new.php';
            if(!file_exists($log_file)) {
                $buff = '<?php exit();?>'."\n";
            }

            $buff .= sprintf("%s\t%s\n\t%0.6f sec\tquery_id:%s\n\n",
                date("Y-m-d H:i"), $currentSummary['query'],
                $stopWatch->getLapTime(), $currentSummary['query_id']);

            if($fp = fopen($log_file, 'a')) {
                fwrite($fp, $buff);
                fclose($fp);
            }
        }

    }
}