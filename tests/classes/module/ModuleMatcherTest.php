<?php

if(!defined('_XE_PATH_')) define('_XE_PATH_', realpath(dirname(__FILE__).'/../../../').'/');

require_once _XE_PATH_ . 'classes/module/ModuleMatcher.class.php';

class moduleModel
{

}

class documentModel
{

}

class ModuleMatcherTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        /*
        $site_module_info->module_srl = 63;
        $site_module_info->module_category_srl = 0;
        $site_module_info->layout_srl = 62;
        $site_module_info->mlayout_srl = 0;
        $site_module_info->use_mobile = "N";
        $site_module_info->menu_srl = 0;
        $site_module_info->skin = "default";
        $site_module_info->browser_title = "Welcome page";

        $site_module_info->domain = 'http://localhost';
        $site_module_info->index_module_srl = 63;
        $site_module_info->document_srl = 64;
        */
    }

    /**
     * Test accessing root url directly
     * http://www.xpressengine.org
     */
    public function testGetModuleInfo_DefaultModule()
    {
        $matcher = new ModuleMatcher();

        $moduleModel = new moduleModel();
        $documentModel = new documentModel();

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        $site_module_info->module_site_srl = 0;
        $site_module_info->module = 'page';
        $site_module_info->mid = "welcome_page";

        $defaultUrl = 'http://www.xpressengine.org';

        $match = $matcher->getModuleInfo(null,null, null, null, null, null, $moduleModel, $site_module_info, $documentModel, $defaultUrl);

        $this->assertEquals($match->module, "page");
        $this->assertEquals($match->act, null);
        $this->assertEquals($match->mid, "welcome_page");
        $this->assertEquals($match->document_srl, null);
        $this->assertEquals($match->module_srl, null);
        $this->assertEquals($match->entry, null);
    }

    /**
     * Test accessing a module by mid
     * http://www.xpressengine.org/welcome_page
     */
    public function testGetModuleInfo_ModuleByMid()
    {
        $matcher = new ModuleMatcher();

        $welcome_page_module_info = new stdClass();
        $welcome_page_module_info->site_srl = 0;
        $welcome_page_module_info->module = 'page';
        $welcome_page_module_info->mid = "welcome_page";

        $moduleModelMock = $this->getMock('moduleModel',array('getModuleInfoByMid'));
        $moduleModelMock
            ->expects($this->any())
            ->method("getModuleInfoByMid")
            ->will($this->returnValue($welcome_page_module_info));


        $documentModel = new documentModel();

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;

        $defaultUrl = 'http://www.xpressengine.org';

        $match = $matcher->getModuleInfo(null,null, "welcome_page", null, null, null, $moduleModelMock, $site_module_info, $documentModel, $defaultUrl);

        $this->assertEquals($match->module, "page");
        $this->assertEquals($match->act, null);
        $this->assertEquals($match->mid, "welcome_page");
        $this->assertEquals($match->document_srl, null);
        $this->assertEquals($match->module_srl, null);
        $this->assertEquals($match->entry, null);
    }

    /**
     * Test default module form virtual site
     * http://shop.xpressengine.org => http://shop.xpressengine.org/demo/shop
     *
     * We expect to receive a DeafultModuleSiteSrlMismatchException
     * which will cause XE to redirect the user to the virtual site URL
     */
    public function testGetModuleInfo_DefaultModuleFromVirtualSite()
    {
        $matcher = new ModuleMatcher();

        $virtual_site_info = new stdClass();
        $virtual_site_info->site_srl = 140;
        $virtual_site_info->domain = "demo";

        $moduleModelMock = $this->getMock('moduleModel',array('getSiteInfo'));
        $moduleModelMock
            ->expects($this->any())
            ->method("getSiteInfo")
            ->will($this->returnValue($virtual_site_info));

        
        $documentModel = new documentModel();

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        $site_module_info->module_site_srl = 140;
        $site_module_info->mid = "shop";

        $defaultUrl = 'http://shop.xpressengine.org';

        try
        {
            $matcher->getModuleInfo(null,null, null, null, null, null, $moduleModelMock, $site_module_info, $documentModel, $defaultUrl);
        }
        catch(Exception $e)
        {
            $this->assertEquals("DefaultModuleSiteSrlMismatchException", get_class($e));
            $this->assertEquals("demo", $e->getDomain());
            $this->assertEquals("shop", $e->getMid());
        }
    }


    public function testGetAct_DefaultAct()
    {
        $module_matcher = new ModuleMatcher();

        // Data loaded from corresponding module.xml
        $xml_info = new stdClass();
        $xml_info->default_index_act = "dispPageIndex";

        $act = $module_matcher->getActionName(null, 'page', $xml_info);

        $this->assertEquals("dispPageIndex", $act);
    }

    /**
     * Act given in URL
     * /?act=dispPageAdminContent
     */
    public function testGetAct_ExplicitlySpecified()
    {
        $module_matcher = new ModuleMatcher();

        // Data loaded from corresponding module.xml
        $xml_info = new stdClass();
        $xml_info->default_index_act = "dispPageIndex";
        $xml_info->action = new stdClass();
        $xml_info->action->dispPageAdminContent = new stdClass();

        $act = $module_matcher->getActionName('dispPageAdminContent', 'page', $xml_info);

        $this->assertEquals("dispPageAdminContent", $act);
    }

    /**
     * An invalid act when XE was already installed should be let as is
     * That's because the controller action could also be specified as
     * an action forward, so there will be subsequent searches based on
     * this value
     */
    public function testGetAct_InvalidActWhenAlreadyInstalled()
    {
        $module_matcher = new ModuleMatcher();

        // Data loaded from corresponding module.xml
        $xml_info = new stdClass();
        $xml_info->default_index_act = "dispPageIndex";
        $xml_info->action = new stdClass();
        $xml_info->action->dispPageAdminContent = new stdClass();

        $act = $module_matcher->getActionName('myPageContent', 'page', $xml_info);

        $this->assertEquals("myPageContent", $act);
    }

    /**
     * An invalid act while XE wasn't already installed
     * should redirect to the default action for the module
     */
    public function testGetAct_InvalidActDuringInstallation()
    {
        $module_matcher = new ModuleMatcher();

        // Data loaded from corresponding module.xml
        $xml_info = new stdClass();
        $xml_info->default_index_act = "dispPageIndex";
        $xml_info->action = new stdClass();
        $xml_info->action->dispPageAdminContent = new stdClass();

        $act = $module_matcher->getActionName('myPageContent', 'install', $xml_info);

        $this->assertEquals("dispPageIndex", $act);
    }

    /**
     * When no act is specified and no default action is found
     * the method throws an Exception
     */
    public function testGetAct_MissingDefaultAction()
    {
        $module_matcher = new ModuleMatcher();

        // Data loaded from corresponding module.xml
        $xml_info = new stdClass();
        $xml_info->default_index_act = null;

        $this->setExpectedException("ModuleDoesNotExistException");

        $module_matcher->getActionName(null, 'page', $xml_info);
    }

    public function testGetKind_Frontend()
    {
        $module_matcher = new ModuleMatcher();

        $kind = $module_matcher->getKind("dispPageIndex", "page");
        $this->assertEquals('', $kind);
    }

    public function testGetKind_Backend()
    {
        $module_matcher = new ModuleMatcher();

        $kind = $module_matcher->getKind("dispPageAdminIndex", "page");
        $this->assertEquals('admin', $kind);
    }

    public function testGetKind_ModuleIsAdmin()
    {
        $module_matcher = new ModuleMatcher();

        $kind = $module_matcher->getKind("dispPageIndex", "admin");
        $this->assertEquals('admin', $kind);
    }

    /**
     * index.php?module=admin&act=dispMemberAdminList
     */
    public function testGetModuleInstance_WhenModuleIsRetrievedFromAct()
    {


    }

}
