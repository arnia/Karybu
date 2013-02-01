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

    private function getContextMockForFileUploads($is_uploaded_file = true, $multiple_files = false)
    {
        if(!$multiple_files)
        {
            $myFiles = array(
                "product_image" => array(
                    "name" => "400.png",
                    "type" => "image/png",
                    "tmp_name" => "/tmp/abcdef",
                    "error" => 0,
                    "size" => 15726
                ));
        }
        else
        {
            $myFiles = array(
                "product_image" => array(
                    "name" => array("400.png"),
                    "type" => array("image/png"),
                    "tmp_name" => array("/tmp/abcdef"),
                    "error" => array(0),
                    "size" => array(15726)
                ));
        }

        // Mock just the isUploadedFile, getFiles and getRequestContentType methods
        $context = $this->getMock('Context', array('is_uploaded_file', 'getFiles', 'getRequestContentType'));
        $context
            ->expects($this->any())
            ->method('is_uploaded_file')
            ->will($this->returnValue($is_uploaded_file));

        $context
            ->expects($this->any())
            ->method('getFiles')
            ->will($this->returnValue($myFiles));

        $context
            ->expects($this->any())
            ->method('getRequestContentType')
            ->will($this->returnValue('multipart/form-data'));

        return $context;
    }


    /**
     * If $_FILES has data, but request type is GET, nothing should happen
     */
    public function testSetArguments_FileUpload_GET_Request()
    {
        $context = new Context();
        $context->setRequestMethod('GET');

        $context->_setUploadedArgument();

        $this->assertEquals(false, $context->is_uploaded);
        $this->assertEquals(new stdClass(), $context->getRequestVars());
    }

    /**
     * If request method is POST, but data sent is not multipart/form-data
     * nothing should happen
     */
    public function testSetArguments_FileUpload_NotMultipartFormData()
    {
        $context = $this->getMock('Context', array('getRequestContentType'));

        $context
            ->expects($this->any())
            ->method('getRequestContentType')
            ->will($this->returnValue('text/html'));

        $context->setRequestMethod('POST');
        $context->_setUploadedArgument();

        $this->assertEquals(false, $context->is_uploaded);
        $this->assertEquals(new stdClass(), $context->getRequestVars());
    }

    /**
     * If $_FILES is empty, do nothing
     */
    public function testSetArguments_FileUpload_EmptyFILES()
    {
        $context = $this->getMock('Context', array('getFiles'));

        $context
            ->expects($this->any())
            ->method('getFiles')
            ->will($this->returnValue(null));

        $context->setRequestMethod('POST');
        $context->_setUploadedArgument();

        $this->assertEquals(false, $context->is_uploaded);
        $this->assertEquals(new stdClass(), $context->getRequestVars());
    }

    /**
     * Test that arguments are properly intialized one just one file
     * was uploaded
     */
    public function testSetArguments_FileUpload_JustOneFile()
    {
        $context = $this->getContextMockForFileUploads();
        $context->setRequestMethod("POST");

        $context->_setUploadedArgument();

        $this->assertEquals(true, $context->is_uploaded);

        $data = new stdClass();
        $data->product_image = array(
            "name" => "400.png",
            "type" => "image/png",
            "tmp_name" => "/tmp/abcdef",
            "error" => 0,
            "size" => 15726
        );

        $arguments = $context->getRequestVars();
        $this->assertEquals($data, $arguments);


        $arguments = $context->getAll();
        $this->assertEquals($data, $arguments);
    }

    /**
     * Test that arguments are not initialized when just one file
     * was uploaded, but it is invalid
     */
    public function testSetArguments_FileUpload_JustOneFile_FakeUpload()
    {
        $context = $this->getContextMockForFileUploads(false);

        $context->setRequestMethod("POST");

        $context->_setUploadedArgument();

        $this->assertEquals(false, $context->is_uploaded);
        $this->assertEquals(new stdClass(), $context->getRequestVars());
    }

    /**
     * Test that arguments were properly initialized when more than one file was uploaded
     */
    public function testSetArguments_FileUpload_Array()
    {
        $context = $this->getContextMockForFileUploads(true, true);
        $context->setRequestMethod("POST");

        $context->_setUploadedArgument();

        $data = new stdClass();
        $data->product_image = array(
            array(
                "name" => "400.png",
                "type" => "image/png",
                "tmp_name" => "/tmp/abcdef",
                "error" => 0,
                "size" => 15726
            ));

        $arguments = $context->getRequestVars();
        $this->assertEquals($data, $arguments);

        $arguments = $context->getAll();
        $this->assertEquals($data, $arguments);
    }

    /**
     * Test that the is_uploaded property is updated for multiple file uploads
     */
    public function testSetArguments_FileUpload_Array_SetsIsUploaded()
    {
        $context = $this->getContextMockForFileUploads(true, true);
        $context->setRequestMethod("POST");

        $context->_setUploadedArgument();

        $data = new stdClass();
        $data->product_image = array(
            array(
                "name" => "400.png",
                "type" => "image/png",
                "tmp_name" => "/tmp/abcdef",
                "error" => 0,
                "size" => 15726
            ));

        $this->assertEquals(true, $context->is_uploaded);
    }

    /**
     * Test that arguments are not initialized if this was a fake upload
     * (check using is_uploaded_file php function)
     */
    public function testSetArguments_FileUpload_Array_FakeUpload()
    {
        $context = $this->getContextMockForFileUploads(false, true);

        $context->setRequestMethod("POST");

        $context->_setUploadedArgument();

        $this->assertEquals(false, $context->is_uploaded);
        $this->assertEquals(new stdClass(), $context->getRequestVars());
    }

    private function getContextMockForDbInfoLoading($db_info, $site_module_info = null)
    {
        $context = $this->getMock('Context', array('getDbInfoFromConfigFile', 'isInstalled', 'getSiteModuleInfo'));
        $context
            ->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));
        $context
            ->expects($this->any())
            ->method('getDbInfoFromConfigFile')
            ->will($this->returnValue($db_info));

        if($site_module_info == null) $site_module_info = new stdClass();
        $context
            ->expects($this->any())
            ->method('getSiteModuleInfo')
            ->will($this->returnValue($site_module_info));
        return $context;
    }

    /**
     * Test app configuration
     */
    public function testLoadDbInfo_DefaultValues()
    {
        $db_info = new stdClass();
        $db_info->master_db = array('db_type' => 'mysql','db_port' => '3306','db_hostname' => 'localhost','db_userid' => 'root','db_password' => 'password','db_database' => 'globalcms','db_table_prefix' => 'xe_');
        $db_info->slave_db = array(array('db_type' => 'mysql','db_port' => '3306','db_hostname' => 'localhost','db_userid' => 'root','db_password' => 'password','db_database' => 'globalcms','db_table_prefix' => 'xe_'));
        $db_info->default_url = 'http://globalcms/';
        $db_info->lang_type = 'en';
        $db_info->use_rewrite = 'Y';
        $db_info->time_zone = '+0200';

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);
        $expected_db_info->time_zone = '+0200';
        $expected_db_info->use_prepared_statements = 'Y';
        $expected_db_info->qmail_compatibility = 'N';
        $expected_db_info->use_db_session = 'N';
        $expected_db_info->use_ssl = 'none';

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info, $actual_db_info);
    }

    /**
     * Test app configuration - prepared statements
     */
    public function testLoadDbInfo_PreparedStatements()
    {
        // Test that the default value for this is Y
        $db_info = new stdClass();
        $db_info->master_db = array('something');

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);
        $expected_db_info->use_prepared_statements = 'Y';

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->use_prepared_statements, $actual_db_info->use_prepared_statements);

        // Test that when value is manually set, it is not overridden
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->use_prepared_statements = 'N';

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->use_prepared_statements, $actual_db_info->use_prepared_statements);
    }

    /**
     * Test app configuration - time zone
     */
    public function testLoadDbInfo_TimeZone()
    {
        // Test that the default value for this is date('0')
        $db_info = new stdClass();
        $db_info->master_db = array('something');

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);
        $expected_db_info->time_zone = date('O');

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->time_zone, $actual_db_info->time_zone);

        // Test that when value is already set in db.config.php, it is not overridden
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->time_zone = '+0200';

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->time_zone, $actual_db_info->time_zone);

        // Make sure time_zone is available in Globals
        $this->assertEquals($context->getGlobals('_time_zone'), $actual_db_info->time_zone);
    }

    /**
     * Test app configuration - Qmail compatibility
     */
    public function testLoadDbInfo_QmailCompatibility()
    {
        // Test that the default value for this is date('0')
        $db_info = new stdClass();
        $db_info->master_db = array('something');

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);
        $expected_db_info->qmail_compatibility = 'N';

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->qmail_compatibility, $actual_db_info->qmail_compatibility);

        // Test that when value is already set in db.config.php, it is not overridden
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->qmail_compatibility = 'Y';

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->qmail_compatibility, $actual_db_info->qmail_compatibility);

        // Make sure time_zone is available in Globals
        $this->assertEquals($context->getGlobals('_qmail_compatibility'), $actual_db_info->qmail_compatibility);
    }

    /**
     * Test app configuration - use db session
     */
    public function testLoadDbInfo_UseDbSession()
    {
        // Test that the default value for this is 'N'
        $db_info = new stdClass();
        $db_info->master_db = array('something');

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);
        $expected_db_info->use_db_session = 'N';

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->use_db_session, $actual_db_info->use_db_session);

        // Test that when value is already set in db.config.php, it is not overridden
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->use_db_session = 'Y';

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->use_db_session, $actual_db_info->use_db_session);
    }

    /**
     * Test app configuration - use SSL
     *
     * The available values for this are: none, optional and always
     * (look for 'ssl_options' in project files)
     */
    public function testLoadDbInfo_UseSSL()
    {
        // Test that the default value for this is date('0')
        $db_info = new stdClass();
        $db_info->master_db = array('something');

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);
        $expected_db_info->use_ssl = 'none';

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->use_ssl, $actual_db_info->use_ssl);

        // Test that when value is already set in db.config.php, it is not overridden
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->use_ssl = 'always';

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->use_ssl, $actual_db_info->use_ssl);

        // Make sure time_zone is available in Context
        $this->assertEquals($context->get('_use_ssl'), $actual_db_info->use_ssl);
    }

    /**
     * Test app configuration - HTTP and HTTPS port
     */
    public function testLoadDbInfo_HTTPS_Port()
    {
        // Test that the default value is to skip these attributes
        $db_info = new stdClass();
        $db_info->master_db = array('something');

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals(null, $actual_db_info->http_port);
        $this->assertEquals(null, $actual_db_info->https_port);

        // Test that when value is already set in db.config.php, it is not overridden
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->http_port = '80';
        $db_info->https_port = '25';

        $context = $this->getContextMockForDbInfoLoading($db_info);
        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = clone($db_info);

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->http_port, $actual_db_info->http_port);
        $this->assertEquals($expected_db_info->https_port, $actual_db_info->https_port);
        $this->assertEquals($actual_db_info->http_port, $context->get('_http_port'));
        $this->assertEquals($actual_db_info->https_port, $context->get('_https_port'));
    }


    /**
     * Test app configuration - missing master_db
     *
     * This is used for legacy apps - that had the db connection string
     * directly as attributes to $db_info, instead of as two arrays
     * (before XE 1.5)
     */
    public function testLoadDbInfo_MasterDbMissing()
    {
        // Test that the default value for this is date('0')
        $db_info = new stdClass();
        $db_info->db_type = 'mysql';
        $db_info->db_port = '3306';
        $db_info->db_hostname = 'localhost';
        $db_info->db_userid = 'root';
        $db_info->db_password = 'password';
        $db_info->db_database = 'globalcms';
        $db_info->db_table_prefix = 'xe_';

        $context = $this->getMock('Context'
            , array('getDbInfoFromConfigFile', 'isInstalled', 'getInstallController', 'getSiteModuleInfo'));

        $context
            ->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));
        $context
            ->expects($this->any())
            ->method('getDbInfoFromConfigFile')
            ->will($this->returnValue($db_info));
        $context
            ->expects($this->any())
            ->method('getSiteModuleInfo')
            ->will($this->returnValue(new stdClass()));

        $installController = $this->getMock('installController', array('makeConfigFile'));
        $installController
            ->expects($this->once())
            ->method('makeConfigFile');

        $context
            ->expects($this->once())
            ->method('getInstallController')
            ->will($this->returnValue($installController));

        $this->assertEquals(null, $context->getDbInfo());

        $expected_db_info = new stdClass();
        $expected_db_info->master_db = array('db_type' => 'mysql','db_port' => '3306','db_hostname' => 'localhost','db_userid' => 'root','db_password' => 'password','db_database' => 'globalcms','db_table_prefix' => 'xe_');
        $expected_db_info->slave_db = array(array('db_type' => 'mysql','db_port' => '3306','db_hostname' => 'localhost','db_userid' => 'root','db_password' => 'password','db_database' => 'globalcms','db_table_prefix' => 'xe_'));

        $context->loadDbInfo();
        $actual_db_info = $context->getDbInfo();

        $this->assertEquals($expected_db_info->master_db, $actual_db_info->master_db);
        $this->assertEquals($expected_db_info->slave_db, $actual_db_info->slave_db);
    }

    /**
     * Check that current site info is properly set
     * And that if we are on a virtual site, the vid is also initialized
     */
    public function testInitializeCurrentSiteInfo_SetSiteModuleInfoInContext()
    {
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->default_url = 'http://www.xpressengine.org';

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        $site_module_info->domain = 'http://www.xpressengine.org';

        $context = $this->getContextMockForDbInfoLoading($db_info, $site_module_info);
        $context->loadDbInfo();
        $context->initializeCurrentSiteInformation($site_module_info);

        $expected_module_info = clone($site_module_info);
        $actual_site_module_info = $context->get('site_module_info');

        $this->assertEquals($expected_module_info, $actual_site_module_info);
    }

    /**
     * Check that current site info is properly set
     * And that if we are on a virtual site, the vid is also initialized
     */
    public function testInitializeCurrentSiteInfo_SetDefaultLanguage()
    {
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->default_url = 'http://www.xpressengine.org';

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        $site_module_info->domain = 'http://www.xpressengine.org';

        // Test that default language is 'en', when nothing else is set
        $context = $this->getContextMockForDbInfoLoading($db_info, $site_module_info);
        $context->loadDbInfo();
        $context->initializeCurrentSiteInformation($site_module_info);

        $db_info = $context->getDbinfo();

        $this->assertEquals('en', $db_info->lang_type);

        // Test that default language persists when manually set
        $site_module_info->default_language = 'ro';

        $context = $this->getContextMockForDbInfoLoading($db_info, $site_module_info);
        $context->loadDbInfo();
        $context->initializeCurrentSiteInformation($site_module_info);

        $db_info = $context->getDbinfo();

        $this->assertEquals('ro', $db_info->lang_type);
    }

    /**
     * Check that current site info is properly set
     * And that if we are on a virtual site, the vid is also initialized
     */
    public function testInitializeCurrentSiteInfo_DifferentDefaultUrl()
    {
        // 1. Arrange
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->default_url = 'http://demo.xpressengine.org';

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        $site_module_info->domain = 'http://www.xpressengine.org';

        $context = $this->getContextMockForDbInfoLoading($db_info, $site_module_info);

        // 2. Act
        $context->loadDbInfo();
        $context->initializeCurrentSiteInformation($site_module_info);

        // 3. Assert
        // Make sure the default_url defined in db.config.php has precedence
        $actual_site_module_info = $context->get('site_module_info');
        $this->assertEquals('http://demo.xpressengine.org', $actual_site_module_info->domain);
    }

    /**
     * Check that current site info is properly set
     * And that if we are on a virtual site, the vid is also initialized
     */
    public function testInitializeCurrentSiteInfo_VirtualSite()
    {
        // 1. Arrange
        $db_info = new stdClass();
        $db_info->master_db = array('something');
        $db_info->default_url = 'http://demo.xpressengine.org';

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 123;
        $site_module_info->domain = 'mysite';

        $context = $this->getMock('Context', array('getDbInfoFromConfigFile', 'isInstalled', 'isSiteID', 'getSiteModuleInfo'));
        $context
            ->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));
        $context
            ->expects($this->any())
            ->method('getDbInfoFromConfigFile')
            ->will($this->returnValue($db_info));
        $context
            ->expects($this->any())
            ->method('getSiteModuleInfo')
            ->will($this->returnValue($site_module_info));
        $context
            ->expects($this->any())
            ->method('isSiteID')
            ->will($this->returnValue(true));

        // 2. Act
        $context->loadDbInfo();
        $context->initializeCurrentSiteInformation($site_module_info);

        // 3. Assert
        $vid = $context->get('vid');
        $this->assertEquals($site_module_info->domain, $vid);
    }

}

/* End of file ContextTest.php */
/* Location: ./tests/classes/context/ContextTest.php */

