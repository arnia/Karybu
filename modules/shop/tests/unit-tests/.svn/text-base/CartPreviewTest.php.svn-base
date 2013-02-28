<?php
	require_once dirname(__FILE__) . "/../lib/Shop_Generic_Tests.class.php";
	require_once dirname(__FILE__) . '/../lib/Bootstrap.php';
	require_once dirname(__FILE__) . '/../../libs/repositories/CartRepository.php';
	require_once dirname(__FILE__) . '/../../shop.info.php';

class CartPreviewTest extends Shop_Generic_Tests_DatabaseTestCase
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
				array('cart_srl' => '774','product_srl' => '133','quantity' => '1','title' => 'Cutie depozitare diferite modele', 'price'=>14.99),
				array('cart_srl' => '774','product_srl' => '130','quantity' => '1','title' => 'Cutie din lemn', 'price'=>29.99),
				array('cart_srl' => '774','product_srl' => '134','quantity' => '1','title' => 'Cutie depozitare diferite modele', 'price'=>14.99),
				array('cart_srl' => '774','product_srl' => '135','quantity' => '1','title' => 'Cutie din lemn', 'price'=>29.99)
			),
			'xe_shop_products' => array(
				array('product_srl' => '130','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie din lemn','description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','short_description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','sku' => 'MOL9505','weight' => '0','status' => 'enabled','friendly_url' => 'MOL9505','price' => '29.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'MOL9505_5784.jpg','related_products' => NULL,'regdate' => '20120904144739','last_update' => '20120923191329','discount_price' => '0','is_featured' => 'Y'),
				array('product_srl' => '133','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie depozitare diferite modele','description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','short_description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','sku' => 'NRUOMY742C','weight' => '0','status' => 'enabled','friendly_url' => 'NRUOMY742C','price' => '14.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'turta-dulce.jpg','related_products' => NULL,'regdate' => '20120904144841','last_update' => '20120926171804','discount_price' => '0','is_featured' => 'Y'),
				array('product_srl' => '134','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie din lemn','description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','short_description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','sku' => 'MOL9505','weight' => '0','status' => 'enabled','friendly_url' => 'MOL9505','price' => '29.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'MOL9505_5784.jpg','related_products' => NULL,'regdate' => '20120904144739','last_update' => '20120923191329','discount_price' => '0','is_featured' => 'Y'),
				array('product_srl' => '135','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie depozitare diferite modele','description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','short_description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','sku' => 'NRUOMY742C','weight' => '0','status' => 'enabled','friendly_url' => 'NRUOMY742C','price' => '14.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'turta-dulce.jpg','related_products' => NULL,'regdate' => '20120904144841','last_update' => '20120926171804','discount_price' => '0','is_featured' => 'Y')
			),
			'xe_shop_shipping_methods' => array(
				array('id' => '768','name' => 'flat_rate_shipping','display_name' => 'Flat Rate Shipping','status' => '1','props' => 'O:8:"stdClass":2:{s:4:"type";s:9:"per_order";s:5:"price";s:2:"10";}','module_srl' => '107')
			),
			'xe_shop' => array(
				array('module_srl' => '107','member_srl' => '4','shop_title' => '','shop_content' => '','profile_content' => '','input_email' => 'R','input_website' => 'R','timezone' => '+0300','currency' => 'EUR','VAT' => 19,'telephone' => NULL,'address' => NULL,'regdate' => '20120831171133','currency_symbol' => '€','discount_min_amount' => NULL,'discount_type' => NULL,'discount_amount' => NULL,'discount_tax_phase' => 'pre_taxes','out_of_stock_products' => 'Y','minimum_order' => NULL,'show_VAT' => NULL,'menus' => 'a:2:{s:11:"header_menu";s:3:"108";s:11:"footer_menu";s:3:"393";}')
			),
			'xe_sites' => array(
				array('site_srl' => '106','index_module_srl' => '107','domain' => 'shop','default_language' => 'en','regdate' => '20120831171133')
			),
			'xe_modules' => array(
				array('module_srl' => '107','module' => 'shop','module_category_srl' => '0','layout_srl' => '0','use_mobile' => 'N','mlayout_srl' => '0','menu_srl' => '108','site_srl' => '106','mid' => 'shop','is_skin_fix' => 'Y','skin' => 'default','mskin' => NULL,'browser_title' => 'admin\'s Shop','description' => '','is_default' => 'N','content' => NULL,'mcontent' => NULL,'open_rss' => 'Y','header_text' => '','footer_text' => '','regdate' => '20120831171133')
			)
		));
	}

	/**
	 * Test cart preview features when all products in cart are available
	 */
	public function testCartPreview_AllAvailable()
	{
		$cart_srl = 774;
		$cart = new Cart($cart_srl);

		// Make sure we start with 4 products
		$this->assertEquals(4, count($cart->getProducts()));

		$cart_preview = new CartPreview($cart, 2);

		$cart_preview_products = $cart_preview->getProducts();
		$this->assertEquals(2, count($cart_preview_products));

		$cart_products_count = $cart_preview->getCartProductsCount();
		$this->assertEquals(4, $cart_products_count);
	}

	/**
	 * Test cart preview total product count when the same product is used multiple times
	 */
	public function testCartPreviewCount_DuplicateProducts()
	{
		$cart_srl = 774;
		$cart = new Cart($cart_srl);

		// Make sure we start with 4 products
		$this->assertEquals(4, count($cart->getProducts()));

		// Add one new product to cart (of the same type as one that already is in the cart)
		$product_repository = new ProductRepository();
		$product = $product_repository->getProduct(133);
		$cart->addProduct($product, 1);

		// Make sure new product was added to cart
		$cart = new Cart($cart_srl);
		$this->assertEquals(5, $cart->count(TRUE));

		$cart_preview = new CartPreview($cart, 2);

		$cart_preview_products = $cart_preview->getProducts();
		$this->assertEquals(2, count($cart_preview_products));

		$cart_products_count = $cart_preview->getCartProductsCount();
		$this->assertEquals(5, $cart_products_count);
	}

	/**
	 * Test empty cart
	 */
	public function testCartPreviewWhenCartIsEmpty()
	{
		// Start with an empty cart
		$cart = new Cart();
		$cart->module_srl = 123;
		$cart->save();
		$this->assertEquals(0, count($cart->getProducts()));

		// Check that Cart preview product count is also 0
		$cart_preview = new CartPreview($cart);
		$this->assertEquals(0, $cart_preview->getCartProductsCount());
	}

	public function testCartPreviewWithDiscount()
	{
		$cart_srl = 774;
		$module_srl = 107;

		// Configure shop to use discounts
		$args = new stdClass();
		$args->module_srl = $module_srl;
		$args->discount_min_amount = 10;
		$args->discount_type = 'fixed_amount';
		$args->discount_amount = 5;
		$output = executeQuery('shop.updateDiscountInfo',$args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage());
		}

		// Test that cart preview has discount
		$cart = new Cart($cart_srl);
		$cart_preview = new CartPreview($cart);
		$this->assertEquals($cart->getDiscountAmount(), $cart_preview->getDiscountAmount());
	}
}