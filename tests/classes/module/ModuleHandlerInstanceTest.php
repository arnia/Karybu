<?php

if(!defined('__XE__')) require dirname(__FILE__).'/../../Bootstrap.php';

require_once _XE_PATH_ . 'classes/object/Object.class.php';
require_once _XE_PATH_ . 'classes/context/ContextInstance.class.php';
require_once _XE_PATH_ . 'classes/handler/Handler.class.php';
require_once _XE_PATH_ . 'classes/module/ModuleHandlerInstance.class.php';

class FileHandler {};
class FrontendFileHandler {}
class Validator {}

class ModuleHandlerInstanceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        error_reporting(-1 ^ E_NOTICE);
        PHPUnit_Framework_Error_Notice::$enabled = false;
    }

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
        $result = $module_handler->init();

        $this->assertFalse((bool)$result);
    }

    public function testInit_WithMidAndEntry_WhenDocumentExists()
    {
        // Arrange
        /** @var $context ContextInstance */
        $context = $this->getMock('ContextInstance', array('isInstalled', 'isExistsSSLAction', 'getServerRequestHttps', 'getServerHost', 'getServerRequestUri', 'setRedirectResponseTo', 'getModuleController'));
        $context->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        $mobile = $this->getMock('MobileInstance');

        $moduleController = $this->getMock('moduleController', array('replaceDefinedLangCode'));
        $moduleController->expects($this->any())->method('replaceDefinedLangCode')
            ->will($this->returnCallback(function($name) { return $name;}));
        $context->expects($this->any())->method('getModuleController')->will($this->returnValue($moduleController));

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;

        $context->set('mid', 'wiki');
        $context->set('entry', 'Tutorials');
        $context->set('site_module_info', $site_module_info);

        $module_handler = $this->getMock('ModuleHandlerInstance'
            , array('executeAddon_before_module_init', 'getModuleModel', 'getDocumentModel', 'getDocumentSrlByAlias', 'triggerCall')
            , array($context, $mobile));
        // Since all validations passed, we expect the addons to be executed before init
        $module_handler->expects($this->once())->method('executeAddon_before_module_init');

        // Document model is expected to find the srl of the given document
        $documentModel = $this->getMock('documentModel', array('getDocumentSrlByAlias'));
        $documentModel->expects($this->once())->method('getDocumentSrlByAlias')
            ->will($this->returnValue(1234));
        $module_handler->expects($this->once())->method('getDocumentModel')->will($this->returnValue($documentModel));

        $moduleModel = $this->getMock('moduleModel', array('getModuleInfoByDocumentSrl', 'getModulePartConfig'));

        // Since document_srl was found, we look for the module info of its associated module
        $module_info = new stdClass();
        $module_info->module = 'wiki_module';
        $module_info->mid = 'wiki';
        $module_info->browser_title = 'Hello';
        $module_info->layout_srl = 456;
        $moduleModel->expects($this->once())->method('getModuleInfoByDocumentSrl')->with(1234)
            ->will($this->returnValue($module_info));

        $expected_module_info = clone($module_info);
        $expected_module_info->site_srl = 0;
        $trigger_result = $this->getMock('Object', array('setMessage', 'toBool'));
        $trigger_result->expects($this->once())->method('toBool')->will($this->returnValue(true));
        $module_handler->expects($this->once())->method('triggerCall')
            ->with($this->equalTo('moduleHandler.init'), $this->equalTo('after'), $expected_module_info)
            ->will($this->returnValue($trigger_result));

        // Also, since module_info was found, we setup associated custom html head
        $part_config = new stdClass();
        $part_config->header_script = '<script></script>';
        $moduleModel->expects($this->once())->method('getModulePartConfig')
            ->with($this->equalTo('layout'), $this->equalTo(456))
            ->will($this->returnValue($part_config));

        $module_handler->expects($this->once())->method('getModuleModel')->will($this->returnValue($moduleModel));

        // Act
        $module_handler->init();

        // Assert
        $this->assertEquals($expected_module_info, $module_handler->module_info);

        $this->assertEquals(1234, $context->get('document_srl'));
        $this->assertEquals($module_info->mid, $context->get('mid'));
        $this->assertEquals($expected_module_info, $context->get('current_module_info'));

        $this->assertEquals('wiki_module', $module_handler->module);
        $this->assertEquals('wiki', $module_handler->mid);
        $this->assertEquals('Hello', $context->getBrowserTitle());
        $this->assertEquals("\n<script></script>", $context->getHtmlHeader());
    }


}
