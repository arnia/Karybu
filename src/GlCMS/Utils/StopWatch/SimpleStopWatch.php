<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/14/13
 * Time: 5:50 PM
 * To change this template use File | Settings | File Templates.
 */

namespace GlCMS\Utils\StopWatch;

use GlCMS\Utils\StopWatch\IStopWatch;
use GlCMS\Utils\StopWatch\IStopWatchListener;


class SimpleStopWatch implements IStopWatch{

    private $microsCounter;

    private $started;

    private $lap;
    private $lapTime;
    private $totalTime;

    private $summaries;
    private $startListeners = array();
    private $stopListeners = array();
    private $summaryListeners = array();

    function __construct(){
        $this->init();
    }

    function start()
    {
        if (!$this->started){
            $this->microsCounter = getMicroTime();
            $this->started = true;
            $this->lap++;
            $this->notifyListeners(IStopWatchListener::START_EVENT);
        }
    }

    function stop($currentSummary = null)
    {
        if ($this->started){
            $this->lapTime = getMicroTime()-$this->microsCounter;
            $this->started = false;
            $this->totalTime+=$this->lapTime;
            $this->saveSummary($currentSummary);
            if ($currentSummary != null){
                $this->notifyListeners(IStopWatchListener::SUMMARY_EVENT);
            }
            $this->notifyListeners(IStopWatchListener::STOP_EVENT);
        }
    }

    function reset()
    {
        if (!$this->started){
            $this->init();
        }
    }

    function getLapTime()
    {
        return $this->lapTime;
    }

    function getTotalTime()
    {
        return $this->totalTime;
    }

    function getAllSummaries()
    {
        return $this->summaries;
    }

    function registerListener($eventType, IStopWatchListener $listener)
    {
        switch ($eventType){
            case IStopWatchListener::START_EVENT:
                $this->startListeners[] = $listener;
                break;
            case IStopWatchListener::STOP_EVENT:
                $this->stopListeners[] = $listener;
                break;
            case IStopWatchListener::SUMMARY_EVENT:
                $this->summaryListeners[] = $listener;
                break;
        };
    }

    function getCurrentSummary()
    {
        return $this->summaries[$this->lap];
    }

    function setCurrentSummary($currentSummary)
    {
        $this->saveSummary($currentSummary);
        $this->notifyListeners(IStopWatchListener::SUMMARY_EVENT);
    }

    // ************* PRIVATE AREA ****************

    private function notifyListeners($eventType){
        switch ($eventType){
            case IStopWatchListener::START_EVENT:
                $listeners = $this->startListeners;
                break;
            case IStopWatchListener::STOP_EVENT:
                $listeners = $this->stopListeners;
                break;
            case IStopWatchListener::SUMMARY_EVENT:
                $listeners = $this->summaryListeners;
                break;
        }
        if (isset($listeners)){
            foreach($listeners as $listener){
                /**@var $listener IStopWatchListener */
                $listener->act($eventType, $this);
            }
        }
    }

    private function saveSummary($summary){
        if ($summary == null){
            $summary = array();
        }
        $summary['lap_time'] = $this->lapTime;
        $this->summaries[$this->lap] = $summary;
    }

    private function init(){
        $this->microsCounter = 0;
        $this->started = false;
        $this->lap = 0;
        $this->lapTime = 0;
        $this->totalTime = 0;
        $this->summaries = array();
    }
}