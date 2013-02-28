<?php
require_once dirname(__FILE__) . '/../lib/Bootstrap.php';

abstract class Shop_SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase
{
	protected $captureScreenshotOnFailure = TRUE;
	protected $screenshotPath = '/../logs/screenshots';
	protected $screenshotUrl = 'http://localhost/screenshots';
	protected $xe_root_url;
	protected $mid = 'shop';

	public function __construct()
	{
		parent::__construct();
		$this->screenshotUrl = $GLOBALS['XE_ROOT_URL'] . '/modules/shop/tests/logs/screenshots';
		$this->screenshotPath = dirname(__FILE__) . '/../logs/screenshots';
		$this->xe_root_url = $GLOBALS['XE_ROOT_URL'];
		$this->vid = $GLOBALS['XE_SHOP_VID'];
	}

    protected function assertUrlEndsWith($with, $message=null)
    {
        if (!$message) $message = "Url does not end with '$with'";
        $this->assertStringEndsWith($with, $this->url(), $message);
    }

    protected function assertHomeUrl()
    {
        $this->assertUrlEndsWith("/index.php?act=dispShopHome&vid=$this->vid", "Not home url");
    }

    protected function assertCartCount($n)
    {
        $cartCount = $this->byCssSelector('#cart-button > a > span')->text();
        $this->assertEquals($n, $cartCount, "Cart count not $n");
    }

    /**
     * Retrieves an array of Element instances
     * @param $cssSelector
     * @return array
     */
    protected function getMultiple($cssSelector)
    {
        return $this->elements($this->using('css selector')->value($cssSelector));
    }

    /**
     * adds products to cart using the frontend list form
     * @param array $products
     * @bug https://github.com/sebastianbergmann/phpunit-selenium/issues/190
     */
    protected function addToCart(array $products)
    {
        foreach ($products as $order=>$quantity) {
            $block = $this->getMultiple('.product');
			if(isset($block[$order]))
			{
				$block = $block[$order];
			}
            /** @var $block PHPUnit_Extensions_Selenium2TestCase_Element */
            $addButton = $block->element($this->using('css selector')->value('.add-to-cart'));
            $quantityInput = $block->element($this->using('css selector')->value('.quantity input'));
            //next line fails because of this: https://github.com/sebastianbergmann/phpunit-selenium/issues/190
            //$quantityInput->value($quantity);
            $addButton->click();
        }
    }

    protected function goToCart()
    {
        $this->byCssSelector('#cart-button > a')->click();
    }

    public static function randomStr($n=7, $alphabet='abcdefghijklmnopqrstuvxyz')
    {
        $arr = str_split($alphabet);
        shuffle($arr);
        $arr = array_slice($arr, 0, $n);
        return implode('', $arr);
    }

}