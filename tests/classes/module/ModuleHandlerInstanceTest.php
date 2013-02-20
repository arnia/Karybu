<?php

if(!defined('__XE__')) require dirname(__FILE__).'/../../Bootstrap.php';

require_once _XE_PATH_ . 'classes/context/Context.class.php';
require_once _XE_PATH_ . 'classes/handler/Handler.class.php';
require_once _XE_PATH_ . 'classes/module/ModuleHandler.class.php';

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

    /**
     * Request: /some_mid/entry/some_entry
     * Module info will be retrieved based on the document_srl
     */
    public function testInit_WithMidAndEntry_WhenDocumentExists()
    {
        $mock_helper = new MockHelper($this);

        // Arrange
        // 0. App is installed
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);
        // 1. Current module info (from the database)
        $module_info = new stdClass();
        $module_info->module = 'wiki_module';
        $module_info->mid = 'wiki_mid';
        $module_info->browser_title = 'Hello';
        $module_info->layout_srl = 456;
        $mock_helper->method('moduleModel', 'getModuleInfoByDocumentSrl')->shouldBeCalledWith(1234)->shouldReturn($module_info);
        // 2. Site module info - we are on the main site
        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        // 3. Current document info (from the database)
        $mock_helper->method('documentModel', 'getDocumentSrlByAlias')->shouldReturn(1234);
        // 4. Current layout part config
        $part_config = new stdClass();
        $part_config->header_script = '<script></script>';
        $mock_helper->method('moduleModel', 'getModulePartConfig')->shouldBeCalledWith('layout', 456)->shouldReturn($part_config);

        // Put mocks together
        $mock_helper->method('ContextInstance', 'getModuleController')->shouldReturnMockModuleController();
        $mock_helper->method('ModuleHandlerInstance', 'getDocumentModel')->shouldReturn($mock_helper->getMock('documentModel'));
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');

        // Setup what we expect to receive
        $expected_module_info = clone($module_info);
        $expected_module_info->site_srl = 0;

        // 3. Make sure trigger is called on the expected output
        $mock_helper->method('ModuleHandlerInstance', 'triggerCall')
            ->shouldBeCalledWith('moduleHandler.init', 'after', $expected_module_info)
            ->shouldReturn(new Object());

        // Act - load context, mobile and construct ModuleHandlerInstance
        /** @var $context ContextInstance */
        $context = $mock_helper->getMock('ContextInstance');
        $context->set('mid', 'wiki_mid');
        $context->set('entry', 'Tutorials');
        $context->set('site_module_info', $site_module_info);

        $mobile = $this->getMock('MobileInstance');
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));

        $result = $module_handler->init();

        // Assert
        // 1. Make sure Context is setup
        $this->assertEquals(1234, $context->get('document_srl'));
        $this->assertEquals('wiki_mid', $context->get('mid'));
        $this->assertEquals($expected_module_info, $context->get('current_module_info'));
        $this->assertEquals('Hello', $context->getBrowserTitle());
        $this->assertEquals("\n<script></script>", $context->getHtmlHeader());

        // 2. Make sure ModuleHanlderInstance properties are setup
        $this->assertEquals($expected_module_info, $module_handler->module_info);
        $this->assertEquals('wiki_module', $module_handler->module);
        $this->assertEquals('wiki_mid', $module_handler->mid);
        $this->assertEquals(1234, $module_handler->document_srl);

        // 3. Make sure Init result is true
        $this->assertTrue((bool)$result);
    }

    public function testInit_WithMidAndEntry_WhenDocumentExistsAndBelongsToVirtualSite_ButIsCalledFromMainSite()
    {
        $mock_helper = new MockHelper($this);

        // Arrange
        // 0. App is installed
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);
        // 1. Current module info (from the database)
        $module_info = new stdClass();
        $module_info->module = 'wiki_module';
        $module_info->mid = 'wiki_mid';
        $module_info->browser_title = 'Hello';
        $module_info->layout_srl = 456;
        $module_info->site_srl = 12;
        $mock_helper->method('moduleModel', 'getModuleInfoByDocumentSrl')->shouldBeCalledWith(1234)->shouldReturn($module_info);
        // 2. Site module info - we are on the main site
        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        // 3. Current document info (from the database)
        $mock_helper->method('documentModel', 'getDocumentSrlByAlias')->shouldReturn(1234);
        // 4. Current layout part config
        $part_config = new stdClass();
        $part_config->header_script = '<script></script>';
        $mock_helper->method('moduleModel', 'getModulePartConfig')->shouldBeCalledWith('layout', 456)->shouldReturn($part_config);
        // 5. Virtual site info
        $virtual_site_info = new stdClass();
        $virtual_site_info->site_srl = 12;
        $virtual_site_info->domain = 'http://shop.xpressengine.org';
        $mock_helper->method('moduleModel', 'getSiteInfo')->shouldReturn($virtual_site_info);

        // Put mocks together
        $mock_helper->method('ContextInstance', 'getModuleController')->shouldReturnMockModuleController();
        $mock_helper->method('ModuleHandlerInstance', 'getDocumentModel')->shouldReturn($mock_helper->getMock('documentModel'));
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));
        $mock_helper->method('ContextInstance', 'isCrawler')->shouldReturn(false);

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');
        // 3. Make sure a redirect is done
        $mock_helper->method('ContextInstance', 'getNotEncodedSiteUrl')
            ->shouldBeCalledWith('http://shop.xpressengine.org', 'mid', 'wiki_mid', 'document_srl', '1234', 'module_srl', null, 'entry', 'Tutorials');
        $mock_helper->method('ContextInstance','setRedirectResponseTo')->shouldBeCalled('once')
            ->shouldReturn('redirect_url');

        // Act - load context, mobile and construct ModuleHandlerInstance
        /** @var $context ContextInstance */
        $context = $mock_helper->getMock('ContextInstance');
        $context->set('mid', 'wiki_mid');
        $context->set('entry', 'Tutorials');
        $context->set('site_module_info', $site_module_info);

        $mobile = $this->getMock('MobileInstance');
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));

        $result = $module_handler->init();

        // Assert
        $this->assertFalse((bool)$result);
    }

    public function testInit_WithMidAndEntry_WhenDocumentExistsAndBelongsToMainSite_ButIsCalledFromVirtualSite()
    {
        $mock_helper = new MockHelper($this);

        // Arrange
        // 0. App is installed
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);
        // 1. Current module info (from the database)
        $module_info = new stdClass();
        $module_info->module = 'wiki_module';
        $module_info->mid = 'wiki_mid';
        $module_info->browser_title = 'Hello';
        $module_info->layout_srl = 456;
        $module_info->site_srl = 0;
        $mock_helper->method('moduleModel', 'getModuleInfoByDocumentSrl')->shouldBeCalledWith(1234)->shouldReturn($module_info);
        // 2. Site module info - we are on a virtual site
        $site_module_info = new stdClass();
        $site_module_info->site_srl = 12;
        // 3. Current document info (from the database)
        $mock_helper->method('documentModel', 'getDocumentSrlByAlias')->shouldReturn(1234);
        // 4. Current layout part config
        $part_config = new stdClass();
        $part_config->header_script = '<script></script>';
        $mock_helper->method('moduleModel', 'getModulePartConfig')->shouldBeCalledWith('layout', 456)->shouldReturn($part_config);
        // 5. Set up default_url
        $db_info = new stdClass();
        $db_info->default_url = 'http://www.xpressengine.org';
        $mock_helper->method('ContextInstance', 'getDbInfo')->shouldReturn($db_info);

        // Put mocks together
        $mock_helper->method('ContextInstance', 'getModuleController')->shouldReturnMockModuleController();
        $mock_helper->method('ModuleHandlerInstance', 'getDocumentModel')->shouldReturn($mock_helper->getMock('documentModel'));
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));
        $mock_helper->method('ContextInstance', 'isCrawler')->shouldReturn(false);

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');
        // 3. Make sure a redirect is done
        $mock_helper->method('ContextInstance', 'getNotEncodedSiteUrl')
            ->shouldBeCalledWith('http://www.xpressengine.org', 'mid', 'wiki_mid', 'document_srl', '1234', 'module_srl', null, 'entry', 'Tutorials');
        $mock_helper->method('ContextInstance','setRedirectResponseTo')->shouldBeCalled('once')
            ->shouldReturn('redirect_url');

        // Act - load context, mobile and construct ModuleHandlerInstance
        /** @var $context ContextInstance */
        $context = $mock_helper->getMock('ContextInstance');
        $context->set('mid', 'wiki_mid');
        $context->set('entry', 'Tutorials');
        $context->set('site_module_info', $site_module_info);

        $mobile = $this->getMock('MobileInstance');
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));

        $result = $module_handler->init();

        // Assert
        $this->assertFalse((bool)$result);
    }


    public function testInit_WithMidAndEntry_WhenDocumentExistsAndBelongsToMainSite_ButIsCalledFromVirtualSite_WhenDefaultUrlNotSet()
    {
        $mock_helper = new MockHelper($this);

        // Arrange
        // 0. App is installed
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);
        // 1. Current module info (from the database)
        $module_info = new stdClass();
        $module_info->module = 'wiki_module';
        $module_info->mid = 'wiki_mid';
        $module_info->browser_title = 'Hello';
        $module_info->layout_srl = 456;
        $module_info->site_srl = 0;
        $mock_helper->method('moduleModel', 'getModuleInfoByDocumentSrl')->shouldBeCalledWith(1234)->shouldReturn($module_info);
        // 2. Site module info - we are on a virtual site
        $site_module_info = new stdClass();
        $site_module_info->site_srl = 12;
        // 3. Current document info (from the database)
        $mock_helper->method('documentModel', 'getDocumentSrlByAlias')->shouldReturn(1234);
        // 4. Current layout part config
        $part_config = new stdClass();
        $part_config->header_script = '<script></script>';
        $mock_helper->method('moduleModel', 'getModulePartConfig')->shouldBeCalledWith('layout', 456)->shouldReturn($part_config);
        // 5. Set up default_url
        $db_info = new stdClass();
        $mock_helper->method('ContextInstance', 'getDbInfo')->shouldReturn($db_info);

        // Put mocks together
        $mock_helper->method('ContextInstance', 'getModuleController')->shouldReturnMockModuleController();
        $mock_helper->method('ModuleHandlerInstance', 'getDocumentModel')->shouldReturn($mock_helper->getMock('documentModel'));
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));
        $mock_helper->method('ContextInstance', 'isCrawler')->shouldReturn(false);
        $mock_helper->method('ContextInstance', 'getLang')->shouldBeCalledWith('msg_default_url_is_not_defined')
            ->shouldReturn('msg_default_url_is_not_defined');

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');

        // Act - load context, mobile and construct ModuleHandlerInstance
        /** @var $context ContextInstance */
        $context = $mock_helper->getMock('ContextInstance');
        $context->set('mid', 'wiki_mid');
        $context->set('entry', 'Tutorials');
        $context->set('site_module_info', $site_module_info);

        $mobile = $this->getMock('MobileInstance');
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));

        $result = $module_handler->init();

        // Assert
        $this->assertEquals('msg_default_url_is_not_defined', $result); // TODO Fix this, it should return false and set $module_handler->error instead
    }

    public function testInit_WithMidAndEntry_WhenDocumentExists_ButItDoesntHaveAnAssociatedModule()
    {
        $mock_helper = new MockHelper($this);

        // Arrange
        // 0. App is installed
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);
        // 1. Current module info (from the database) - can only be retrieved by mid
        $module_info = new stdClass();
        $module_info->module = 'wiki_module';
        $module_info->mid = 'wiki_mid';
        $module_info->browser_title = 'Hello';
        $module_info->layout_srl = 456;
        $mock_helper->method('moduleModel', 'getModuleInfoByDocumentSrl')->shouldBeCalledWith(1234)->shouldReturn(null);
        $mock_helper->method('moduleModel', 'getModuleInfoByMid')->shouldBeCalledWith('wiki_mid')->shouldReturn($module_info);
        // 2. Site module info - we are on the main site
        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        // 3. Current document info (from the database)
        $mock_helper->method('documentModel', 'getDocumentSrlByAlias')->shouldReturn(1234);
        // 4. Current layout part config
        $part_config = new stdClass();
        $part_config->header_script = '<script></script>';
        $mock_helper->method('moduleModel', 'getModulePartConfig')->shouldBeCalledWith('layout', 456)->shouldReturn($part_config);

        // Put mocks together
        $mock_helper->method('ContextInstance', 'getModuleController')->shouldReturnMockModuleController();
        $mock_helper->method('ModuleHandlerInstance', 'getDocumentModel')->shouldReturn($mock_helper->getMock('documentModel'));
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');

        // Setup what we expect to receive
        $expected_module_info = clone($module_info);
        $expected_module_info->site_srl = 0;

        // 3. Make sure trigger is called on the expected output
        $mock_helper->method('ModuleHandlerInstance', 'triggerCall')
            ->shouldBeCalledWith('moduleHandler.init', 'after', $expected_module_info)
            ->shouldReturn(new Object());

        // Act - load context, mobile and construct ModuleHandlerInstance
        /** @var $context ContextInstance */
        $context = $mock_helper->getMock('ContextInstance');
        $context->set('mid', 'wiki_mid');
        $context->set('entry', 'Tutorials');
        $context->set('site_module_info', $site_module_info);

        $mobile = $this->getMock('MobileInstance');
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));

        $result = $module_handler->init();

        // Assert
        // 1. Make sure Context is setup
        $this->assertEquals(1234, $context->get('document_srl'));
        $this->assertEquals('wiki_mid', $context->get('mid'));
        $this->assertEquals($expected_module_info, $context->get('current_module_info'));
        $this->assertEquals('Hello', $context->getBrowserTitle());
        $this->assertEquals("\n<script></script>", $context->getHtmlHeader());

        // 2. Make sure ModuleHandlerInstance properties are setup
        $this->assertEquals($expected_module_info, $module_handler->module_info);
        $this->assertEquals('wiki_module', $module_handler->module);
        $this->assertEquals('wiki_mid', $module_handler->mid);
        $this->assertEquals(null, $module_handler->document_srl);

        // 3. Make sure Init result is true
        $this->assertTrue((bool)$result);
    }

    public function testInit_WithMidAndEntry_WhenDocumentExists_ButItHasADifferentAssociatedModule()
    {
        $mock_helper = new MockHelper($this);

        // Arrange
        // 0. App is installed
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);
        // 1. Current module info (from the database)
        $module_info = new stdClass();
        $module_info->module = 'wiki_module';
        $module_info->mid = 'another_wiki_mid';
        $module_info->browser_title = 'Hello';
        $module_info->layout_srl = 456;
        $mock_helper->method('moduleModel', 'getModuleInfoByDocumentSrl')->shouldBeCalledWith(1234)->shouldReturn($module_info);
        // 2. Site module info - we are on the main site
        $site_module_info = new stdClass();
        $site_module_info->domain = 'http://www.xpressengine.org';
        $site_module_info->site_srl = 0;
        // 3. Current document info (from the database)
        $mock_helper->method('documentModel', 'getDocumentSrlByAlias')->shouldReturn(1234);

        // Put mocks together
        $mock_helper->method('ModuleHandlerInstance', 'getDocumentModel')->shouldReturn($mock_helper->getMock('documentModel'));
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');
        // 3. Make sure the redirect url is not encoded
        $mock_helper->method('ContextInstance', 'getNotEncodedSiteUrl')
            ->shouldBeCalledWith($site_module_info->domain, 'mid', $module_info->mid, 'document_srl', 1234)
            ->shouldReturn('redirect_url');
        // 3. Make sure a Redirect is performed
        $mock_helper->method('ContextInstance', 'setRedirectResponseTo')
            ->shouldBeCalledWith('redirect_url');

        // Act - load context, mobile and construct ModuleHandlerInstance
        /** @var $context ContextInstance */
        $context = $mock_helper->getMock('ContextInstance');
        $context->set('mid', 'wiki_mid');
        $context->set('entry', 'Tutorials');
        $context->set('site_module_info', $site_module_info);

        $mobile = $this->getMock('MobileInstance');
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));

        $result = $module_handler->init();

        // Assert
        // Make sure Init result is false
        $this->assertFalse((bool)$result);
    }

    /**
     * Request: /some_mid/entry/some_entry
     * 'some_entry; document does not exist
     *
     * Module info will be retrieved based on the mid
     */
    public function testInit_WithMidAndEntry_WhenDocumentDoesntExist()
    {
        $mock_helper = new MockHelper($this);

        // Arrange
        // 0. App is installed
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);
        // 1. Current module info (from the database)
        $module_info = new stdClass();
        $module_info->module = 'wiki_module';
        $module_info->mid = 'wiki_mid';
        $module_info->browser_title = 'Hello';
        $module_info->layout_srl = 456;
        $mock_helper->method('moduleModel', 'getModuleInfoByMid')->shouldBeCalledWith('wiki_mid')->shouldReturn($module_info);
        // 2. Site module info - we are on the main site
        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        // 3. Current document info (from the database)
        $mock_helper->method('documentModel', 'getDocumentSrlByAlias')->shouldReturn(null);
        // 4. Current layout part config
        $part_config = new stdClass();
        $part_config->header_script = '<script></script>';
        $mock_helper->method('moduleModel', 'getModulePartConfig')->shouldBeCalledWith('layout', 456)->shouldReturn($part_config);

        // Put mocks together
        $mock_helper->method('ContextInstance', 'getModuleController')->shouldReturnMockModuleController();
        $mock_helper->method('ModuleHandlerInstance', 'getDocumentModel')->shouldReturn($mock_helper->getMock('documentModel'));
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');

        // Setup what we expect to receive
        $expected_module_info = clone($module_info);
        $expected_module_info->site_srl = 0;

        // 3. Make sure trigger is called on the expected output
        $mock_helper->method('ModuleHandlerInstance', 'triggerCall')
            ->shouldBeCalledWith('moduleHandler.init', 'after', $expected_module_info)
            ->shouldReturn(new Object());

        /** @var $context ContextInstance */
        $context = $mock_helper->getMock('ContextInstance');
        $mobile = $this->getMock('MobileInstance');

        // Arrange - 5. Request arguments
        $context->set('mid', 'wiki_mid');
        $context->set('entry', 'Tutorials');
        $context->set('site_module_info', $site_module_info);

        // Act - load context, mobile and construct ModuleHandlerInstance
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));
        $result = $module_handler->init();

        // Assert
        // 1. Make sure Context is setup
        $this->assertEquals('wiki_mid', $context->get('mid'));
        $this->assertEquals($expected_module_info, $context->get('current_module_info'));
        $this->assertEquals('Hello', $context->getBrowserTitle());
        $this->assertEquals("\n<script></script>", $context->getHtmlHeader());

        // 2. Make sure ModuleHanlderInstance properties are setup
        $this->assertEquals($expected_module_info, $module_handler->module_info);
        $this->assertEquals('wiki_module', $module_handler->module);
        $this->assertEquals('wiki_mid', $module_handler->mid);

        // 3. Make sure Init result is true
        $this->assertTrue((bool)$result);
    }

    private function getModuleHandlerInstance_ForInitTest_WhenModuleInfoNotFound(&$context, $request_arg_name, $request_arg_value)
    {
        $mock_helper = new MockHelper($this);

        // Arrange
        // 0. App is installed
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);
        // 1. Current module info (from the database)
        $mock_helper->method('moduleModel', 'getModuleInfoByDocumentSrl')->shouldBeCalledWith(123)->shouldReturn(null);
        // 2. Site module info - we are on the main site
        $site_module_info = new stdClass();
        $site_module_info->module = 'wiki_module';
        $site_module_info->mid = 'wiki_mid';
        $site_module_info->browser_title = 'Hello';
        $site_module_info->layout_srl = 456;
        $site_module_info->site_srl = 0;
        // 4. Current layout part config
        $part_config = new stdClass();
        $part_config->header_script = '<script></script>';
        $mock_helper->method('moduleModel', 'getModulePartConfig')->shouldBeCalledWith('layout', 456)->shouldReturn($part_config);
        $mock_helper->method('moduleModel', 'getModuleInfoByMid')->shouldReturn(null); // for second test case

        // Put mocks together
        $mock_helper->method('ContextInstance', 'getModuleController')->shouldReturnMockModuleController();
        $mock_helper->method('ModuleHandlerInstance', 'getDocumentModel')->shouldReturn($mock_helper->getMock('documentModel'));
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS');
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init');
        $mock_helper->method('ModuleHandlerInstance', 'triggerCall')->shouldReturn(new Object());

        /** @var $context ContextInstance */
        $context = $mock_helper->getMock('ContextInstance');
        $mobile = $this->getMock('MobileInstance');

        $context->set('site_module_info', $site_module_info);
        $context->set($request_arg_name, $request_arg_value);

        return $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));
    }

    public function testInit_WhenModuleInfoNotFound_DefaultModuleShouldBeUsed_RequestByDocumentSrl()
    {
        $context = null;
        // Act
        $module_handler = $this->getModuleHandlerInstance_ForInitTest_WhenModuleInfoNotFound($context, 'document_srl', '123');
        $result = $module_handler->init();

        // Assert
        // 1. Make sure Context is setup
        $this->assertEquals('Hello', $context->getBrowserTitle());
        $this->assertEquals("\n<script></script>", $context->getHtmlHeader());

        // 2. Make sure ModuleHandlerInstance properties are setup
        $this->assertEquals('wiki_module', $module_handler->module);

        // 3. Make sure Init result is true
        $this->assertTrue((bool)$result);
    }

    public function testInit_WhenModuleInfoNotFound_DefaultModuleShouldBeUsed_RequestByModule()
    {
        $context = null;
        // Act
        $module_handler = $this->getModuleHandlerInstance_ForInitTest_WhenModuleInfoNotFound($context, 'module', 'some_module');
        $result = $module_handler->init();

        $expected_module_info = new stdClass();
        $expected_module_info->mid = null;
        $expected_module_info->module = 'some_module';
        $expected_module_info->site_srl = 0;
        $this->assertTrue((bool)$result);
        $this->assertEquals($expected_module_info, $context->get('current_module_info'));
    }

    public function testInit_WhenModuleInfoNotFound_DefaultModuleShouldBeUsed_RequestByMid()
    {
        $context = null;
        // Act
        $module_handler = $this->getModuleHandlerInstance_ForInitTest_WhenModuleInfoNotFound($context, 'mid', 'some_mid');
        $result = $module_handler->init();

        $expected_module_info = new stdClass();
        $expected_module_info->mid = 'some_mid';
        $expected_module_info->module = null;
        $expected_module_info->site_srl = 0;
        $this->assertTrue((bool)$result);
        $this->assertEquals('some_mid', $context->get('mid'));
        $this->assertEquals($expected_module_info, $context->get('current_module_info'));
        $this->assertEquals('msg_module_is_not_exists', $module_handler->error);
        $this->assertEquals('404', $module_handler->httpStatusCode);
    }

    public function testInit_WhenModuleInfoNotFound_DefaultModuleShouldBeUsed_RequestByMid_VirtualSite()
    {
        $mock_helper = new MockHelper($this);

        // Arrange
        // 0. App is installed
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);
        // 1. Current module info (from the database)
        $mock_helper->method('moduleModel', 'getModuleInfoByDocumentSrl')->shouldBeCalledWith(123)->shouldReturn(null);
        // 2. Site module info - we are on the main site
        $site_module_info = new stdClass();
        $site_module_info->module = 'wiki_module';
        $site_module_info->mid = 'wiki_mid';
        $site_module_info->browser_title = 'Hello';
        $site_module_info->layout_srl = 456;
        $site_module_info->site_srl = 12;
        $site_module_info->module_site_srl = 12;
        // 4. Current layout part config
        $part_config = new stdClass();
        $part_config->header_script = '<script></script>';
        $mock_helper->method('moduleModel', 'getModulePartConfig')->shouldBeCalledWith('layout', 456)->shouldReturn($part_config);
        $mock_helper->method('moduleModel', 'getModuleInfoByMid')->shouldReturn(null); // for second test case

        // Put mocks together
        $mock_helper->method('ContextInstance', 'getModuleController')->shouldReturnMockModuleController();
        $mock_helper->method('ModuleHandlerInstance', 'getDocumentModel')->shouldReturn($mock_helper->getMock('documentModel'));
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS');
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init');
        $mock_helper->method('ModuleHandlerInstance', 'triggerCall')->shouldReturn(new Object());

        /** @var $context ContextInstance */
        $context = $mock_helper->getMock('ContextInstance');
        $mobile = $this->getMock('MobileInstance');

        $context->set('site_module_info', $site_module_info);
        $context->set('mid', 'some_mid');

        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));
        $result = $module_handler->init();

        // 1. Make sure Context is setup
        $this->assertEquals('wiki_mid', $context->get('mid'));
        $this->assertEquals($site_module_info, $context->get('current_module_info'));
        $this->assertEquals('Hello', $context->getBrowserTitle());
        $this->assertEquals("\n<script></script>", $context->getHtmlHeader());

        // 2. Make sure ModuleHandlerInstance properties are setup
        $this->assertEquals('wiki_module', $module_handler->module);

        // 3. Make sure Init result is true
        $this->assertTrue((bool)$result);
    }

    public function testInit_WithModule()
    {
        $mock_helper = new MockHelper($this);
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);

        // Prepare mocks
        $context = $mock_helper->getMock('ContextInstance');
        $mobile = $this->getMock('MobileInstance');
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));

        // Arrange - Request arguments
        $context->set('module', 'admin');

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');
        // 3. Make sure trigger is called
        $mock_helper->method('ModuleHandlerInstance', 'triggerCall')->shouldBeCalled('once')->shouldReturn(new Object());

        // Act
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));
        $result = $module_handler->init();

        // Assert
        $expected_module_info = new stdClass();
        $expected_module_info->module = 'admin';
        $expected_module_info->mid = null;
        $expected_module_info->site_srl = null;

        // 1. Make sure Context is setup
        $this->assertEquals($expected_module_info, $context->get('current_module_info'));

        // 2. Make sure ModuleHanlderInstance properties are setup
        $this->assertEquals($expected_module_info, $module_handler->module_info);
        $this->assertEquals('admin', $module_handler->module);

        // 3. Make sure Init result is true
        $this->assertTrue((bool)$result);
    }


    public function testInit_WhenTriggersFailShouldReturnFalse()
    {
        $mock_helper = new MockHelper($this);
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);

        // Prepare mocks
        $context = $mock_helper->getMock('ContextInstance');
        $mobile = $this->getMock('MobileInstance');
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));

        // Arrange - Request arguments
        $context->set('module', 'admin');

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');
        // 3. Make sure trigger is called
        $mock_helper->method('Object', 'toBool')->shouldReturn(false);
        $mock_helper->method('Object', 'getMessage')->shouldReturn('Some error');
        $mock_helper->method('ModuleHandlerInstance', 'triggerCall')
            ->shouldBeCalled('once')->shouldReturn($mock_helper->getMock('Object'));

        // Act
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));
        $result = $module_handler->init();

        // Assert
        $this->assertFalse((bool)$result);
        $this->assertEquals('Some error', $module_handler->error);
    }

    public function testInit_WhenDefaultModuleBelongsToVirtualSite()
    {
        $mock_helper = new MockHelper($this);
        $mock_helper->method('ContextInstance', 'isInstalled')->shouldReturn(true);

        // Assert
        // 1. Make sure input was validated
        $mock_helper->method('ModuleHandlerInstance', 'validateVariablesAgainstXSS')->shouldBeCalled('once');
        // 2. Make sure the before_module_init addon is executed
        $mock_helper->method('ModuleHandlerInstance', 'executeAddon_before_module_init')->shouldBeCalled('once');
        // 3. Make sure we are redirecting to the right thing
        $mock_helper->method('ContextInstance', 'getNotEncodedSiteUrl')
            ->shouldBeCalledWith('http://shop.xpressengine.org', 'mid', 'default_module_mid')
            ->shouldReturn('redirect_url');
        $mock_helper->method('ContextInstance', 'setRedirectResponseTo')->shouldBeCalled('once')
            ->shouldBeCalledWith('redirect_url');

        // Prepare mocks
        $context = $mock_helper->getMock('ContextInstance');
        $mobile = $this->getMock('MobileInstance');

        // Arrange
        // 1. Site module info - we are on the main site
        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        $site_module_info->module_site_srl = 123;
        $site_module_info->mid = 'default_module_mid';
        $context->set('site_module_info', $site_module_info);
        // 2. Module site info - default module belongs to different site
        $module_site_info = new stdClass();
        $module_site_info->domain = 'http://shop.xpressengine.org';
        $mock_helper->method('moduleModel', 'getSiteInfo')->shouldBeCalledWith(123)->shouldReturn($module_site_info);
        $mock_helper->method('ModuleHandlerInstance', 'getModuleModel')->shouldReturn($mock_helper->getMock('moduleModel'));

        // Act
        $module_handler = $mock_helper->getMock('ModuleHandlerInstance', array($context, $mobile));
        $result = $module_handler->init();

        // Assert - Make sure Init result is false
        $this->assertFalse((bool)$result);
    }


}
