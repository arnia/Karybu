<?php
    require_once dirname(__FILE__) . "/../lib/Shop_Generic_Tests.class.php";
    require_once dirname(__FILE__) . '/../lib/Bootstrap.php';
    require_once dirname(__FILE__) . '/../../shop.info.php';

class OrderTest extends Shop_Generic_Tests_DatabaseTestCase
{
    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_cart' => array(
                array('cart_srl' => '774','module_srl' => '107','member_srl' => '4','session_id' => 'u1d0efs24bm05no5s2tgjspvo6','billing_address_srl' => '253','shipping_address_srl' => '253','items' => '2','extra' => '{"price":44.979999542236,"shipping_method":"flat_rate_shipping","payment_method":"cash_on_delivery"}','regdate' => '20120929183309','last_update' => '20120929183309')
            ),
            'xe_shop_cart_products' => array(
                array('cart_srl' => '774','product_srl' => '133','quantity' => '1','title' => 'Cutie depozitare diferite modele'),
                array('cart_srl' => '774','product_srl' => '130','quantity' => '1','title' => 'Cutie din lemn')
            ),
            'xe_shop_products' => array(
                array('product_srl' => '130','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie din lemn','description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','short_description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','sku' => 'MOL9505','weight' => '0','status' => 'enabled','friendly_url' => 'MOL9505','price' => '29.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'MOL9505_5784.jpg','related_products' => NULL,'regdate' => '20120904144739','last_update' => '20120923191329','discount_price' => '0','is_featured' => 'Y'),
                array('product_srl' => '133','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie depozitare diferite modele','description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','short_description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','sku' => 'NRUOMY742C','weight' => '0','status' => 'enabled','friendly_url' => 'NRUOMY742C','price' => '14.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'turta-dulce.jpg','related_products' => NULL,'regdate' => '20120904144841','last_update' => '20120926171804','discount_price' => '0','is_featured' => 'Y')
            ),
            'xe_shop_shipping_methods' => array(
                array('id' => '768','name' => 'flat_rate_shipping','display_name' => 'Flat Rate Shipping','status' => '1','props' => 'O:8:"stdClass":2:{s:4:"type";s:9:"per_order";s:5:"price";s:2:"10";}','module_srl' => '107')
            ),
            'xe_shop' => array(
                array('module_srl' => '107','member_srl' => '4','shop_title' => '','shop_content' => '','profile_content' => '','input_email' => 'R','input_website' => 'R','timezone' => '+0300','currency' => 'EUR','VAT' => NULL,'telephone' => NULL,'address' => NULL,'regdate' => '20120831171133','currency_symbol' => '€','discount_min_amount' => NULL,'discount_type' => NULL,'discount_amount' => NULL,'discount_tax_phase' => NULL,'out_of_stock_products' => 'Y','minimum_order' => NULL,'show_VAT' => NULL,'menus' => 'a:2:{s:11:"header_menu";s:3:"108";s:11:"footer_menu";s:3:"393";}')
            ),
            'xe_sites' => array(
                array('site_srl' => '106','index_module_srl' => '107','domain' => 'shop','default_language' => 'en','regdate' => '20120831171133')
            ),
            'xe_modules' => array(
                array('module_srl' => '107','module' => 'shop','module_category_srl' => '0','layout_srl' => '0','use_mobile' => 'N','mlayout_srl' => '0','menu_srl' => '108','site_srl' => '106','mid' => 'shop','is_skin_fix' => 'Y','skin' => 'default','mskin' => NULL,'browser_title' => 'admin\'s Shop','description' => '','is_default' => 'N','content' => NULL,'mcontent' => NULL,'open_rss' => 'Y','header_text' => '','footer_text' => '','regdate' => '20120831171133')
            )
        ));
    }

    public function testCreateOrderFromCart()
    {
        $cart_srl = 774;

		try
		{
			$cart = new Cart($cart_srl);
			$order = new Order($cart);
			$order->save();
			$order->saveCartProducts($cart);
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}


        $order_repository = new OrderRepository();
        $order = $order_repository->getOrderBySrl($order->order_srl);

        $this->assertEquals(count($cart->getProducts()), count($order->getProducts()));
        $this->assertEquals($cart->getShippingMethodName(), $order->shipping_method);
        $this->assertEquals($cart->getShippingCost(), $order->shipping_cost);
        $this->assertEquals($cart->getPaymentMethodName(), $order->payment_method);
        $this->assertEquals($cart->getTotal(), $order->total);
    }

}