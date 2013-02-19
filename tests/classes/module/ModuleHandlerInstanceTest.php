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
        $context = $this->getMock('ContextInstance', array('isInstalled', 'convertEncodingStr'));
        $context->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));
        $context->expects($this->any())
            ->method('convertEncodingStr')
            ->will($this->returnCallback(function($value) { return 'encoded_' . $value; }));

        $context->set('module', 'my_module');
        $context->set('act', 'my_act');
        $context->set('mid', 'my_mid');
        $context->set('document_srl', '1234');
        $context->set('module_srl', '5678');
        $context->set('entry', 'my_entry');

        $mobile = $this->getMock('MobileInstance');

        $module_handler = new ModuleHandlerInstance($context, $mobile);

        $this->assertEquals('my_module', $module_handler->module);
        $this->assertEquals('my_act', $module_handler->act);
        $this->assertEquals('my_mid', $module_handler->mid);
        $this->assertEquals('1234', $module_handler->document_srl);
        $this->assertEquals('5678', $module_handler->module_srl);
        $this->assertEquals('encoded_my_entry', $module_handler->entry);
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

    private function setupAndTestInvalidInput($key, $value)
    {
        $context = $this->getMock('ContextInstance', array('isInstalled', 'close'));
        $context->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        $mobile = $this->getMock('MobileInstance');

        // Assert Context->close is called
        $context->expects($this->once())->method('close');

        // Arrange - set up invalid mid
        $context->set($key, $value);

        $module_handler = $this->getMock('ModuleHandlerInstance', array('printInvalidRequestHtmlPage', 'terminateScript'), array($context, $mobile));
        // Assert invalid request message is printed
        $module_handler->expects($this->once())->method('printInvalidRequestHtmlPage');
        $module_handler->expects($this->once())->method('terminateScript');

        // Act - call init
        $module_handler->validateVariablesAgainstXSS();
    }

    public function testValidateVariablesAgainstXSS_InvalidMid()
    {
        $this->setupAndTestInvalidInput('mid', 'some<script>alert()</script>thing');
    }

    public function testValidateVariablesAgainstXSS_InvalidModule()
    {
        $this->setupAndTestInvalidInput('module', 'some<script>alert()</script>thing');
    }

    public function testValidateVariablesAgainstXSS_InvalidAct()
    {
        $this->setupAndTestInvalidInput('act', 'some<script>alert()</script>thing');
    }

    public function testValidateVariablesAgainstXSS_InvalidEntry()
    {
        $context = $this->getMock('ContextInstance', array('isInstalled', 'close'));
        $context->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        $mobile = $this->getMock('MobileInstance');

        // Assert Context->close is called
        $context->expects($this->never())->method('close');

        // Arrange - set up invalid mid
        $context->set('entry', 'some<script>alert()</script>thing');

        $module_handler = $this->getMock('ModuleHandlerInstance', array('printInvalidRequestHtmlPage', 'terminateScript'), array($context, $mobile));
        // Assert invalid request message is printed
        $module_handler->expects($this->never())->method('printInvalidRequestHtmlPage');
        $module_handler->expects($this->never())->method('terminateScript');

        // Act - call init
        $module_handler->validateVariablesAgainstXSS();
    }

    public function testInit_RedirectsWhenProtocolIsHTTPSandSLLActExists()
    {
        // Arrange
        $context = $this->getMock('ContextInstance', array('isInstalled', 'isExistsSSLAction', 'getServerRequestHttps', 'getServerHost', 'getServerRequestUri', 'setRedirectResponseTo'));
        $context->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        $mobile = $this->getMock('MobileInstance');

        $context->set('act', 'dispSomeAction');
        $context->set('_use_ssl', 'optional');
        $context->expects($this->once())->method('isExistsSSLAction')->with('dispSomeAction')->will($this->returnValue(true));
        $context->expects($this->once())->method('getServerRequestHttps')->will($this->returnValue('off'));
        $context->expects($this->once())->method('getServerHost')->will($this->returnValue('www.xpressengine.org'));
        $context->expects($this->once())->method('getServerRequestUri')->will($this->returnValue('/?act=dispSomeAction'));

        // Assert that a redirect will be made
        $context->expects($this->once())->method('setRedirectResponseTo')->with('https://www.xpressengine.org/?act=dispSomeAction');

        // Act - call init
        $module_handler = new ModuleHandlerInstance($context, $mobile);
        $module_handler->init();
    }
}
