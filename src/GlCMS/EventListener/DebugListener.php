<?php
namespace GlCMS\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This was designed to be called in dev environment only
 * and should be responsible for triggering logging, statistics, etc.
 * @package GlCMS\EventListener
 */
class DebugListener  implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array();
    }

}