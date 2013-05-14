<?php

use Karybu\ModuleHandler\ModuleInstanceRetriever;

class ModuleInstanceRetrieverTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test accessing root url directly
     * http://www.karybu.org
     */
    public function testGetModuleInfo_DefaultModule()
    {
        $documentModel = $this->getMock('documentModel');
        $moduleModel = $this->getMock('moduleModel');
        $context = $this->getMock('ContextInstance');

        $module_retriever = new ModuleInstanceRetriever($documentModel, $moduleModel, $context);

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        $site_module_info->module_site_srl = 0;
        $site_module_info->module = 'page';
        $site_module_info->mid = "welcome_page";

        $document_srl = null;
        $result = $module_retriever->findModuleInfo(null, null, $document_srl, $site_module_info);

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals($result->getModuleInfo()->module, "page");
        $this->assertEquals($result->getModuleInfo()->mid, "welcome_page");
        $this->assertEquals($document_srl, null);
        $this->assertFalse(isset($result->getModuleInfo()->entry));
    }

    /**
     * Test accessing a module by mid
     * http://www.karybu.org/welcome_page
     */
    public function testGetModuleInfo_ModuleByMid()
    {
        $welcome_page_module_info = new stdClass();
        $welcome_page_module_info->site_srl = 0;
        $welcome_page_module_info->module = 'page';
        $welcome_page_module_info->mid = "welcome_page";
        $moduleModelMock = $this->getMock('moduleModel',array('getModuleInfoByMid'));
        $moduleModelMock
            ->expects($this->any())
            ->method("getModuleInfoByMid")
            ->will($this->returnValue($welcome_page_module_info));

        $documentModel = $this->getMock('documentModel');
        $context = $this->getMock('ContextInstance');

        $module_retriever = new ModuleInstanceRetriever($documentModel, $moduleModelMock, $context);

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        $site_module_info->module_site_srl = 0;
        $document_srl = null;

        $result = $module_retriever->findModuleInfo("welcome_page", null, $document_srl, $site_module_info);

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals($result->getModuleInfo()->module, "page");
        $this->assertEquals($result->getModuleInfo()->mid, "welcome_page");
        $this->assertEquals($document_srl, null);
        $this->assertFalse(isset($result->getModuleInfo()->entry));
    }


    /**
     * Test default module form virtual site
     * http://shop.karybu.org => http://shop.karybu.org/demo/shop
     *
     * We expect to receive a DeafultModuleSiteSrlMismatchException
     * which will cause XE to redirect the user to the virtual site URL
     */
    public function testGetModuleInfo_DefaultModuleFromVirtualSite()
    {
        $virtual_site_info = new stdClass();
        $virtual_site_info->site_srl = 140;
        $virtual_site_info->domain = "demo";
        $moduleModelMock = $this->getMock('moduleModel',array('getSiteInfo'));
        $moduleModelMock
            ->expects($this->any())
            ->method("getSiteInfo")
            ->will($this->returnValue($virtual_site_info));

        $documentModel = $this->getMock('documentModel');
        $context = $this->getMock('ContextInstance');

        $module_retriever = new ModuleInstanceRetriever($documentModel, $moduleModelMock, $context);

        $site_module_info = new stdClass();
        $site_module_info->site_srl = 0;
        $site_module_info->module_site_srl = 140;
        $site_module_info->mid = "shop";
        $document_srl = null;

        $result = $module_retriever->findModuleInfo(null, null, $document_srl, $site_module_info);

        $this->assertFalse($result->isSuccessful());
    }


}