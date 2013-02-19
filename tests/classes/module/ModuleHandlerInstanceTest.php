<?php

if(!defined('__XE__')) require dirname(__FILE__).'/../../Bootstrap.php';

require_once _XE_PATH_ . 'classes/context/ContextInstance.class.php';
require_once _XE_PATH_ . 'classes/handler/Handler.class.php';
require_once _XE_PATH_ . 'classes/module/ModuleHandlerInstance.class.php';

class FileHandler {};
class FrontendFileHandler {}
class Validator {}

class ModuleHandlerInstanceTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor_WhenAppIsInstalled()
    {
        $context = $this->getMock('ContextInstance', array('isInstalled'));
        $context->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));

        $context->set('module', 'my_module');
        $context->set('act', 'my_act');
        $context->set('mid', 'my_mid');
        $context->set('document_srl', 'my_document_srl');
        $context->set('module_srl', 'my_module_srl');

        $mobile = $this->getMock('MobileInstance');

        $module_handler = new ModuleHandlerInstance($context, $mobile);

        $this->assertEquals('my_module', $module_handler->module);
        $this->assertEquals('my_mid', $module_handler->mid);
    }

    public function testConstructor_WhenAppIsNotInstalled()
    {
        $context = $this->getMock('ContextInstance', array('isInstalled'));
        $context->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(false));

        $context->set('module', 'my_module');
        $context->set('mid', 'my_mid');
        $context->set('act', 'my_act');
        $mobile = $this->getMock('MobileInstance');

        $module_handler = new ModuleHandlerInstance($context, $mobile);

        $this->assertEquals('install', $module_handler->module);
        $this->assertEquals(null, $module_handler->mid);
        $this->assertEquals('my_act', $module_handler->act);
    }

    public function testConstructor_WhenContextInitFailed()
    {
        $context = $this->getMock('ContextInstance', array('isInstalled'));
        $context->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));
        $context->isSuccessInit = false;
        $mobile = $this->getMock('MobileInstance');

        $module_handler = new ModuleHandlerInstance($context, $mobile);

        $this->assertEquals('msg_invalid_request', $module_handler->error);
    }




}
