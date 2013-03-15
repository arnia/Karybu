<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/14/13
 * Time: 5:20 PM
 * To change this template use File | Settings | File Templates.
 */

namespace GlCMS\Utils\StopWatch;


interface IStopWatch {

    function start();

    function stop($currentCurrentSummary = null);

    function reset();

    function getLapTime();

    function getTotalTime();

    function getCurrentSummary();

    function setCurrentSummary($currentSummary);

    function getAllSummaries();

    function registerListener($eventType, IStopWatchListener $listener);

}