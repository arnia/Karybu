<?php

if(!defined('__XE__')) require dirname(__FILE__).'/../../Bootstrap.php';

require_once _XE_PATH_.'classes/context/Context.class.php'; 
require_once _XE_PATH_.'classes/handler/Handler.class.php';
require_once _XE_PATH_.'classes/frontendfile/FrontEndFileHandler.class.php';
require_once _XE_PATH_.'classes/file/FileHandler.class.php';
require_once _XE_PATH_.'classes/xml/XmlParser.class.php';

class ContextTest extends PHPUnit_Framework_TestCase
{
	/**
	 * test whether the singleton works
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf('Context', Context::getInstance());
		$this->assertSame(Context::getInstance(), Context::getInstance());
	}

	public function testSetGetVars()
	{
		$this->assertSame(Context::get('var1'), null);
		Context::set('var1', 'val1');
		$this->assertSame(Context::get('var1'), 'val1');

		Context::set('var2', 'val2');
		$this->assertSame(Context::get('var2'), 'val2');
		Context::set('var3', 'val3');
		$data = new stdClass;
		$data->var1 = 'val1';
		$data->var2 = 'val2';
		$this->assertEquals(Context::gets('var1','var2'), $data);
		$data->var3 = 'val3';
		$this->assertEquals(Context::getAll(), $data);
	}

	public function testAddGetBodyClass()
	{
		$this->assertEquals(Context::getBodyClass(), '');
		Context::addBodyClass('red');
		$this->assertEquals(Context::getBodyClass(), ' class="red"');
		Context::addBodyClass('green');
		$this->assertEquals(Context::getBodyClass(), ' class="red green"');
		Context::addBodyClass('blue');
		$this->assertEquals(Context::getBodyClass(), ' class="red green blue"');

		// remove duplicated class
		Context::addBodyClass('red');
		$this->assertEquals(Context::getBodyClass(), ' class="red green blue"');
	}

    public function testRequestMethod_Default()
    {
        $context = new Context();
        $this->assertEquals('GET', $context->getRequestMethod());
    }

    public function testRequestMethod_POST()
    {
        $context = new Context();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('POST', $context->getRequestMethod());
    }

    public function testRequestMethod_XMLRPC()
    {
        $context = new Context();
        $GLOBALS['HTTP_RAW_POST_DATA'] = 'abcde';
        $this->assertEquals('XMLRPC', $context->getRequestMethod());
    }

    public function testRequestMethod_JSON()
    {
        $context = new Context();
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $this->assertEquals('JSON', $context->getRequestMethod());
    }

    public function testRequestMethod_ManuallySet()
    {
        $context = new Context();
        $context->setRequestMethod('POST');
        $this->assertEquals('POST', $context->getRequestMethod());
    }

    public function testResponseMethod_Default()
    {
        $context = new Context();
        $this->assertEquals('HTML', $context->getResponseMethod());
    }

    public function testReponseMethod_WhenRequestIs_JSON()
    {
        $context = new Context();
        $context->setRequestMethod('JSON');
        $this->assertEquals('JSON', $context->getResponseMethod());
    }

    public function testResponseMethod_WhenUserManuallySetsInvalidData()
    {
        $context = new Context();
        $context->setResponseMethod('WRONG_TYPE');
        $this->assertEquals('HTML', $context->getResponseMethod());
    }

    public function testResponseMethod_WhenUserManuallySetsValidData()
    {
        $context = new Context();
        $context->setResponseMethod('XMLRPC');
        $this->assertEquals('XMLRPC', $context->getResponseMethod());
        $context->setResponseMethod('HTML');
        $this->assertEquals('HTML', $context->getResponseMethod());
    }

    /**
     * Test that when variables change in Context they also change in Global context
     * MUST Have for displaying the templates (for now at least)
     */
    public function testChangesInContextAppearInGlobalContext()
    {
        $__Context__ = new stdClass();
        $lang = new stdClass();
        $myCookies = array();

        $context = new Context();
        $context->linkContextToGlobals($__Context__, $lang, $myCookies);

        $context->set('name', 'Joe');
        $this->assertEquals('Joe', $__Context__->name);

        $lang->module_list='Modules List';
        $this->assertEquals('Modules List', $__Context__->lang->module_list);

        $myCookies['XDEBUG_SESSION_START'] = '1234';
        $this->assertEquals('1234', $__Context__->_COOKIE['XDEBUG_SESSION_START']);
    }

    public function testChangesInContextAppearInPHPGlobals()
    {
        $context = new Context();

        $context->linkContextToGlobals(
            $context->getGlobals('__Context__'),
            $context->getGlobals('lang'),
            $context->getGlobalCookies());

        $context->set('name', 'Joe');
        $this->assertEquals('Joe', $context->getGlobals('__Context__')->name);

        global $lang;
        $lang->module_list='Modules List';
        $this->assertEquals('Modules List', $context->getGlobals('lang')->module_list);

        $cookies = &$context->getGlobalCookies();
        $cookies['XDEBUG_SESSION_START'] = '1234';
        $this->assertEquals('1234', $context->getGlobals('__Context__')->_COOKIE['XDEBUG_SESSION_START']);
    }

    /**
     * Test that request arguments are propely initialized when
     * Request type is XMLRPC
     *
     * Data sent:
     *    <?xml version="1.0" encoding="utf-8" ?>
     *    <methodCall>
     *        <params>
     *            <module><![CDATA[admin]]></module>
     *            <act><![CDATA[procAdminRecompileCacheFile]]></act>
     *        </params>
     *    </methodCall>
     *
     * Sample data taken from the "Re-create cache file" button in XE Admin Dashboard footer
     */
    public function testSetArguments_XMLRPC()
    {
        // Set up object that will be returned after parsing input XML
        $module = new Xml_Node_();
        $module->node_name = "module";
        $module->body = "admin";

        $act = new Xml_Node_();
        $act->node_name = "act";
        $act->body = "procAdminRecompileCacheFile";

        $params = new Xml_Node_();
        $params->module = $module;
        $params->act = $act;

        $methodcall = new Xml_Node_();
        $methodcall->params = $params;

        $xml_obj = new stdClass();
        $xml_obj->methodcall = $methodcall;

        $parser = $this->getMock('XmlParser', array('parse'));
        $parser
            ->expects($this->any())
            ->method('parse')
            ->will($this->returnValue($xml_obj));

        $context = new Context();
        $context->setRequestMethod('XMLRPC');

        $context->_setXmlRpcArgument($parser);

        $data = new stdClass();
        $data->module = 'admin';
        $data->act = 'procAdminRecompileCacheFile';

        $arguments = $context->getRequestVars();
        $this->assertEquals($data, $arguments);
        $arguments = $context->getAll();
        $this->assertEquals($data, $arguments);
    }

    /**
     * Test that request arguments are properly initialized when
     * Request type is JSON
     *
     * Data sent:
     *  domain=&module=admin&act=getSiteAllList
     *
     * Sample data taken from Admin, General settings page, the Select Default Module textbox
     * $.exec_json('module.procModuleAdminGetList', {site_srl:$this.data('site_srl')}, on_complete);
     */
    public function testSetArguments_JSON()
    {
        $context = new Context();
        $context->setRequestMethod('JSON');

        $GLOBALS['HTTP_RAW_POST_DATA'] = "domain=&module=admin&act=getSiteAllList";
        $context->_setJSONRequestArgument();

        $data = new stdClass();
        $data->module = 'admin';
        $data->act = 'getSiteAllList';

        $arguments = $context->getRequestVars();
        $this->assertEquals($data, $arguments);

        $data->domain = "";
        $arguments = $context->getAll();
        $this->assertEquals($data, $arguments);

    }


    /**
     * $_REQUEST holds all data in $_GET, $_POST and $_COOKIE
     * Only what is in $_GET and $_POST should be set in the Request vars
     */
    public function testSetArguments_REQUEST()
    {
        $_GET = array("module" => "admin", "act" => "dispLayoutAdminAllInstanceList");
        $_POST = array();
        $_COOKIE = array("XDEBUG_SESSION_START" => "1234");

        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

        $context = new Context();
        $context->_setRequestArgument();

        $data = new stdClass();
        $data->module = 'admin';
        $data->act = 'dispLayoutAdminAllInstanceList';

        $arguments = $context->getRequestVars();
        $this->assertEquals($data, $arguments);

        $data->XDEBUG_SESSION_START = "1234";
        $arguments = $context->getAll();
        $this->assertEquals($data, $arguments);
    }

}

/* End of file ContextTest.php */
/* Location: ./tests/classes/context/ContextTest.php */
