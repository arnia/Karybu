<?php

require_once '../../../MockHelper.php';
require_once __DIR__ . "/../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../classes/module/ModuleKey.php';

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ValidatorListenerTest extends PHPUnit_Framework_TestCase
{
    public function testRulesetIsSubscribedToControllerEvent()
    {
        $validator = $this->getMock('Validator');
        $validator_session = $this->getMock('Karybu\Validator\ValidatorSession');

        $listener = new \Karybu\EventListener\ValidatorListener($validator, $validator_session);
        $events = $listener->getSubscribedEvents();

        $controller_events = array_keys($events[\Symfony\Component\HttpKernel\KernelEvents::CONTROLLER]);
        $this->assertTrue(in_array('setupLegacyDependencies', $controller_events));
    }

    private function getListenerMock($validator, $validator_session)
    {
        $mock_helper = new \MockHelper($this);

        $mock_helper->method('moduleModel', 'getValidatorFilePath')
            ->shouldBeCalledWith("dummymodule", "some_ruleset", "some_mid")
            ->shouldReturn("some_path");
        $module_model = $mock_helper->getMock('moduleModel');

        $mock_helper->method('Karybu\EventListener\ValidatorListener', 'getModuleModel')->shouldReturn($module_model);
        return $mock_helper->getMock('Karybu\EventListener\ValidatorListener', array($validator, $validator_session));
    }

    private function getEventMock($oModule)
    {
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('mid', 'some_mid');

        $kernel = $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface');
        $controller = new \Karybu\HttpKernel\Controller\ControllerWrapper($oModule);
        return new FilterControllerEvent($kernel, $controller, $request, \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST);
    }

    public function testRuleset_WhenEverythingIsOk()
    {
        $mock_helper = new \MockHelper($this);

        $mock_helper->method('Validator', 'validate')->shouldReturn(true);
        $validator = $mock_helper->getMock('Validator');
        $validator_session = $this->getMock('Karybu\Validator\ValidatorSession');

        $mock_helper->method('ModuleObject', 'setMessage');
        $mock_helper->method('ModuleObject', 'stop')->shouldBeCalled("never");
        $oModule = $mock_helper->getMock('ModuleObject');
        $oModule->module_key = new ModuleKey("dummymodule", "view", "");
        $oModule->ruleset = "some_ruleset";

        $listener = $this->getListenerMock($validator, $validator_session);
        $event = $this->getEventMock($oModule);

        $result = $listener->checkRuleset($event);

        $this->assertTrue($result);
    }

    public function testRuleset_WhenValidationFails()
    {
        $mock_helper = new \MockHelper($this);

        $mock_helper->method('Validator', 'validate')->shouldReturn(false);
        $validator = $mock_helper->getMock('Validator');
        $mock_helper->method('Karybu\Validator\ValidatorSession', 'saveError')
            ->shouldBeCalled("once")
            ->shouldBeCalledWith(array(-1, "validation error"));
        $mock_helper->method('Karybu\Validator\ValidatorSession', 'saveRequestVariables')
            ->shouldBeCalled("once");
        $validator_session = $this->getMock('Karybu\Validator\ValidatorSession');

        $mock_helper->method('ModuleObject', 'setMessage');
        $mock_helper->method('ModuleObject', 'stop')->shouldBeCalled("once")->shouldBeCalledWith("validation error");

        $oModule = $mock_helper->getMock('ModuleObject');
        $oModule->module_key = new ModuleKey("dummymodule", "view", "");
        $oModule->ruleset = "some_ruleset";

        $listener = $this->getListenerMock($validator, $validator_session);
        $event = $this->getEventMock($oModule);

        $result = $listener->checkRuleset($event);

        $this->assertFalse($result);
    }

    public function testRuleset_CustomErrorMessages()
    {
        $mock_helper = new \MockHelper($this);

        $mock_helper->method('Validator', 'validate')->shouldReturn(true);
        $mock_helper->method('ValidatorSession', 'setupCustomErrorMessages')->shouldBeCalled("once");
        $validator = $mock_helper->getMock('Validator');
        $validator_session = $this->getMock('Karybu\Validator\ValidatorSession');

        $mock_helper->method('ModuleObject', 'setMessage');
        $mock_helper->method('ModuleObject', 'stop')->shouldBeCalled("never");
        $oModule = $mock_helper->getMock('ModuleObject');
        $oModule->module_key = new ModuleKey("dummymodule", "view", "");
        $oModule->ruleset = "some_ruleset";

        $listener = $this->getListenerMock($validator, $validator_session);
        $event = $this->getEventMock($oModule);

        $result = $listener->checkRuleset($event);

        $this->assertTrue($result);
    }

}