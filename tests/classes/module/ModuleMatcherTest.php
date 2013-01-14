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
    public function testDefaultModule()
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
    public function testModuleByMid()
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
    public function testDefaultModuleFromVirtualSite()
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


}
