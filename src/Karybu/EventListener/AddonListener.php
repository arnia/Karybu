<?php

namespace Karybu\EventListener;

use Karybu\Event\KarybuEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddonListener implements EventSubscriberInterface {

    /** @var \MobileInstance */
    private $mobile;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            KarybuEvents::BEFORE_MODULE_INIT => array(
                array('beforeModuleInit', 50)
            )
        );
    }

    public function __construct(\MobileInstance $mobile)
    {
        $this->mobile = $mobile;
    }

    public function beforeModuleInit()
    {
        // execute addon (before module initialization)
        $called_position = 'before_module_init';
        $oAddonController = & getController('addon');
        $addon_file = $oAddonController->getCacheFilePath($this->mobile->isFromMobilePhone() ? 'mobile' : 'pc');
        $result = include($addon_file);
        if(!$result) {
            throw new \Exception("Some addon failed to execute the BEFORE_MODULE_INIT event");
        }
    }

}