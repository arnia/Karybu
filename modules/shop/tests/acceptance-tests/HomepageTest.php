<?php

require_once dirname(__FILE__) . '/../lib/Shop_SeleniumTestCase.php';

class HomepageTest extends Shop_SeleniumTestCase
{
    protected $startUrl;

	protected function setUp()
	{
        $this->setBrowser('firefox');
		$this->setBrowserUrl($this->xe_root_url);
        $this->startUrl = "$this->xe_root_url?mid=$this->mid&vid=$this->vid";
	}

    public function testInitial()
    {
        $this->assertStringEndsWith('/demo/shop', $this->url(), 'Wrong initial redirection');
        $this->assertEquals("XE Shop Demo", $this->title(), 'Wrong title');
        $this->byCssSelector('#header-logo')->click();
        $this->assertUrlEndsWith("/index.php?act=dispShopHome&vid=$this->vid", 'Wrong home url');
        $this->goToCart();
        $this->assertUrlEndsWith("/index.php?act=dispShopCart&vid=$this->vid", 'Wrong cart url');
    }
}