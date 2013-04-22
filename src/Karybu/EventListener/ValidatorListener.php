<?php

namespace Karybu\EventListener;

use Karybu\Validator\ValidatorSession;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ValidatorListener implements EventSubscriberInterface
{
    /** @var \moduleModel */
    private $module_model;

    /** @var \Validator */
    private $validator;

    /** @var \Karybu\Validator\ValidatorSession */
    private $validator_session;

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
            KernelEvents::CONTROLLER => array(
                array('checkRuleset', 98)
            )
        );
    }

    public function __construct(\Validator $validator, ValidatorSession $validator_session)
    {
        $this->validator = $validator;
        $this->validator_session = $validator_session;
    }

    public function checkRuleset(FilterControllerEvent $event)
    {
        $oModule = $event->getController()->getModuleInstance();
        $ruleset = $oModule->ruleset;

        if(empty($ruleset)) return true;

        $rulesetModule = $oModule->module_key->getModule(); // ? $forward->module : $this->module;
        $mid = $event->getRequest()->attributes->get('mid'); // TODO Make sure this is the same as ModuleHandler->mid
        $rulesetFile = $this->getModuleModel()->getValidatorFilePath($rulesetModule, $ruleset, $mid);

        if(empty($rulesetFile)) return true;

        $this->validator_session->setupCustomErrorMessages();

        // TODO Refactor this bit - not sure this type of injection is ok
        $this->validator->setRulesetPath($rulesetFile);
        $result = $this->validator->validate();
        if (!$result) {
            $lastError = $this->validator->getLastError();
            $errorMsg = $lastError['msg'] ? $lastError['msg'] : 'validation error';

            $oModule->stop($errorMsg); // stop() takes care of setting the error in the module instance

            $this->validator_session->saveError(-1, $errorMsg);
            $this->validator_session->saveRequestVariables();

            return false;
        }

        return true;
    }

    protected function &getModuleModel()
    {
        return getModel('module');
    }

}