<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/15/13
 * Time: 5:37 PM
 * To change this template use File | Settings | File Templates.
 */

namespace GlCMS\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Psr\Log\LoggerInterface;


/**
 * This was designed to be called in dev environment only
 * and should be responsible for triggering logging, statistics, etc.
 * @package GlCMS\EventListener
 */
class DebugListener  implements EventSubscriberInterface{

    private $dbListeners = array();

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('doTriggerDBStatistics', 29)
            ));
    }

    public function doTriggerDBStatistics(GetResponseEvent $event){
        \DB::addSubscribers($this->dbListeners);
        // TODO implement this
    }

    public function addDBListener(EventSubscriberInterface $listener){
        $this->dbListeners[] = $listener;
    }
}