<?php

require_once dirname(__FILE__) . '/../lib/Shop_SeleniumTestCase.php';

class CheckoutTest extends Shop_SeleniumTestCase
{
    protected $startUrl;

    protected function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl($this->xe_root_url);
        $this->startUrl = "$this->xe_root_url?mid=$this->mid&vid=$this->vid";
    }

    public function testOrder()
    {
		$this->url($this->startUrl);
        $this->addToCart(array(
            //product order in list => quantity to add
            0 => 2,
            1 => 2,
            2 => 2,
            3 => 1
        ));
        $this->assertCartCount(4);
        $this->goToCart();
        $differentCartProducts = $this->getMultiple('tr.product');
        $this->assertEquals(4, count($differentCartProducts));
        $checkboxes = $this->getMultiple('.inputCheck.boxlist');
        $checkboxes[1]->click();
        $this->byId('delete_multiple')->click();
        $this->acceptAlert();
        $this->assertCartCount(3);
        $differentCartProducts = $this->getMultiple('tr.product');
        $this->assertEquals(3, count($differentCartProducts));
        $this->byCssSelector('div.checkout a.button')->click();
        //checkout
        $checkoutProducts = $this->getMultiple('#review tbody tr');
        $this->assertEquals(3, count($checkoutProducts));
        $this->byId('new_billing_address_firstname')->value("Testing");
        $this->byId('new_billing_address_lastname')->value("{$this->randomStr(10)}");
        $this->byId('new_billing_address_email')->value("{$this->randomStr()}@{$this->randomStr()}.{$this->randomStr(3)}");
        $this->byId('new_billing_address_telephone')->value("{$this->randomStr(9, '0123456')}");
        $this->byId('new_billing_address_address')->value("{$this->randomStr(80)}");
        $this->byId('payment_cash_on_delivery')->click();
        $this->byCssSelector('form#big')->submit();
        //dispShopPlaceOrder
        $forms = $this->getMultiple('form');
        $forms[1]->submit();
        $this->assertEquals('Order confirmation', $this->byCssSelector('h1')->text(), 'Order not saved');
    }

}