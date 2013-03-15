<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/14/13
 * Time: 6:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace GlCMS\Utils\StopWatch;


interface IStopWatchListener {
    const STOP_EVENT = 1;
    const START_EVENT = 2;
    const SUMMARY_EVENT = 4;
    public function act($eventType, IStopWatch $stopWatch);
}