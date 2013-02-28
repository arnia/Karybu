<?php
require_once dirname(__FILE__) . "/../lib/Shop_Generic_Tests.class.php";
require_once dirname(__FILE__) . '/../lib/Bootstrap.php';
require_once dirname(__FILE__) . '/../../libs/repositories/CartRepository.php';
require_once dirname(__FILE__) . '/../../shop.info.php';

class CartTest extends Shop_Generic_Tests_DatabaseTestCase
{
    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_cart' => array(
                array('cart_srl' => '774','module_srl' => '107','member_srl' => '4','session_id' => 'session1','billing_address_srl' => '253','shipping_address_srl' => '253','items' => '2','extra' => '{"price":44.979999542236,"shipping_method":"flat_rate_shipping","payment_method":"cash_on_delivery"}','regdate' => '20120929183309','last_update' => '20120929183309'),
                array('cart_srl' => '14','module_srl' => '107','member_srl' => NULL,'session_id' => 'anonSession','billing_address_srl' => NULL,'shipping_address_srl' => NULL,'items' => '0','regdate' => '20120929183309','last_update' => '20120929183309')
            ),
            'xe_shop_cart_products' => array(
                array('cart_srl' => '774','product_srl' => '133','quantity' => '1','title' => 'Cutie depozitare diferite modele', 'price'=>14.99),
                array('cart_srl' => '774','product_srl' => '130','quantity' => '1','title' => 'Cutie din lemn', 'price'=>29.99)
            ),
            'xe_shop_products' => array(
                array('product_srl' => '130','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie din lemn','description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','short_description' => 'Bam boo magazinul on-line de cadouri si decoratiuni va recomanda aceasta cutie din lemn cu un design clasic, avand 6 compartimente poate indeplinii mai multe roluri in casa si viata dvs.','sku' => 'MOL9505','weight' => '0','status' => 'enabled','friendly_url' => 'MOL9505','price' => '29.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'MOL9505_5784.jpg','related_products' => NULL,'regdate' => '20120904144739','last_update' => '20120923191329','discount_price' => '0','is_featured' => 'Y'),
                array('product_srl' => '133','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie depozitare diferite modele','description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','short_description' => 'Bam boo, magazinul on-line de decoratiuni si cadouri, va prezinta noua gama de cutii de depozitare din metal in 3 modele simpatice, foarte utile in casa dvs.','sku' => 'NRUOMY742C','weight' => '0','status' => 'enabled','friendly_url' => 'NRUOMY742C','price' => '14.99','qty' => '10','in_stock' => 'Y','primary_image_filename' => 'turta-dulce.jpg','related_products' => NULL,'regdate' => '20120904144841','last_update' => '20120926171804','discount_price' => '0','is_featured' => 'Y'),
                array('product_srl' => '132','member_srl' => '4','module_srl' => '107','parent_product_srl' => NULL,'product_type' => 'simple','title' => 'Cutie neferoasa','description' => 'Pe vremuri se facea si-n Romana','short_description' => 'Pe vremuri','sku' => 'KVZOMY742C','weight' => '15','status' => 'enabled','friendly_url' => 'sasasasasa','price' => '19.4','qty' => '15','in_stock' => 'Y','primary_image_filename' => 'turta-dulce2.jpg','related_products' => NULL,'regdate' => '20120904144841','last_update' => '20120926171804','discount_price' => '16','is_featured' => 'Y')
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

    public function testFirstCount()
    {
        $this->assertEquals(2, $this->getConnection()->getRowCount('xe_shop_cart'), "First count");
    }

    public function testAddCart()
    {
        $cart = new Cart(array(
            'module_srl'    => 307,
            'member_srl'    => NULL,
            'guest_srl'     => 14,
            'session_id'    => NULL,
            'items'         => NULL,
            'regdate'       => '20100424171420',
            'last_update'   => '20100424192420'
        ));

        $cart_repository = new CartRepository();
        $cart_repository->insertCart($cart);
        $this->assertEquals(3, $this->getConnection()->getRowCount('xe_shop_cart'), "Insert failed");
    }

    public function testCartTotal_WithShipping()
    {
        $module_srl = 107;
        $cart_srl = 774;

        $cart = new Cart($cart_srl);

        // 1. Check that cart has expected products
        $this->assertEquals(2, count($cart->getProducts()));

        // 2. Check that shipping method is set and shipping cost is correct
        $this->assertEquals('flat_rate_shipping', $cart->getShippingMethodName());
        $this->assertEquals(10, $cart->getShippingCost());

        // 3. Check that item total is correct
        $this->assertEquals(44.98, $cart->getItemTotal());

		$this->assertEquals($cart->getVATBeforeDiscount(), $cart->getVATAfterDiscount(), '', 0.01);
		$this->assertEquals($cart->getTotalBeforeDiscountWithoutVAT(), $cart->getTotalAfterDiscountWithoutVAT(), '', 0.01);
		$this->assertEquals($cart->getTotalBeforeDiscountWithVAT(), $cart->getTotalAfterDiscountWithVAT(), '', 0.01);
		$this->assertEquals($cart->getTotalBeforeDiscount(), $cart->getTotalAfterDiscount(), '', 0.01);

        // 4. Check global total is correct
        $this->assertEquals(54.98, $cart->getTotal(), '', 0.01);

		// 5. Check VAT
		$this->assertEquals(7.1816, $cart->getVAT(), '', 0.01);
    }

    public function testCartTotal_WithShippingAndDiscountFixedAmount()
    {
        $module_srl = 107;
        $cart_srl = 774;

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

        $cart = new Cart($cart_srl);

        // 1. Check that cart has expected products
        $this->assertEquals(2, count($cart->getProducts()));

        // 2. Check that shipping method is set and shipping cost is correct
        $this->assertEquals('flat_rate_shipping', $cart->getShippingMethodName());
        $this->assertEquals(10, $cart->getShippingCost());

        // 3. Check that item total is correct
        $this->assertEquals(44.98, $cart->getItemTotal(), '', 0.01);

        // 4. Check total before discount is correct
        $this->assertEquals(44.98, $cart->getTotalBeforeDiscount(), '', 0.01);

		// 5. Check total after discount is correct
		$this->assertEquals(39.98, $cart->getTotalAfterDiscount(), '', 0.01);

        // 6. Check global total is correct
        $this->assertEquals(49.98, $cart->getTotal(), '', 0.01);

		// 7. Check VAT
		$this->assertEquals(6.3834, $cart->getVAT(), '', 0.01);
    }

	public function testCartTotal_WithShippingAndDiscountPercentagePostTax()
	{
		$module_srl = 107;
		$cart_srl = 774;

		// Configure shop to use discounts
		$args = new stdClass();
		$args->module_srl = $module_srl;
		$args->discount_min_amount = 5;
		$args->discount_type = Discount::DISCOUNT_TYPE_PERCENTAGE;
		$args->discount_amount = 10;
		$args->discount_tax_phase = Discount::PHASE_AFTER_VAT;

		$output = executeQuery('shop.updateDiscountInfo',$args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage());
		}

		$cart = new Cart($cart_srl);

		// 1. Check that cart has expected products
		$this->assertEquals(2, count($cart->getProducts()));

		// 2. Check that shipping method is set and shipping cost is correct
		$this->assertEquals('flat_rate_shipping', $cart->getShippingMethodName());
		$this->assertEquals(10, $cart->getShippingCost());

		// 3. Check that item total is correct
		$this->assertEquals(44.98, $cart->getItemTotal(), '', 0.01);

		// 4. Check total before discount is correct
		$this->assertEquals(44.98, $cart->getTotalBeforeDiscount(), '', 0.01);

		// 5. Check total before discount is correct
		$this->assertEquals(40.482, $cart->getTotalAfterDiscount(), '', 0.01);

		// 6. Check global total is correct
		$this->assertEquals(50.482, $cart->getTotal(), '', 0.01);

		// 7. Check VAT
		$this->assertEquals(6.4635, $cart->getVAT(), '', 0.01);
	}


	public function testCartTotal_WithShippingAndDiscountPercentagePreTax()
	{
		$module_srl = 107;
		$cart_srl = 774;

		// Configure shop to use discounts
		$args = new stdClass();
		$args->module_srl = $module_srl;
		$args->discount_min_amount = 5;
		$args->discount_type = Discount::DISCOUNT_TYPE_PERCENTAGE;
		$args->discount_amount = 10;
		$args->discount_tax_phase = Discount::PHASE_BEFORE_VAT;

		$output = executeQuery('shop.updateDiscountInfo',$args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage());
		}

		$cart = new Cart($cart_srl);

		// 1. Check that cart has expected products
		$this->assertEquals(2, count($cart->getProducts()));

		// 2. Check that shipping method is set and shipping cost is correct
		$this->assertEquals('flat_rate_shipping', $cart->getShippingMethodName());
		$this->assertEquals(10, $cart->getShippingCost());

		// 3. Check that item total is correct
		$this->assertEquals(44.98, $cart->getItemTotal());

		// 4. Check total before discount is correct
		$this->assertEquals(44.98, $cart->getTotalBeforeDiscount());

		// Step summary:
		//                 | x=Total (with VAT) | y=Total (without VAT) | z=VAT amount
		//                 |                    | y = x / (1 + 19%)     | 19% y
		// ----------------------------------------------------------------------------
		// Before discount |  44.98             |  37.7983              | 7.1816
		// Discount        | - 10% y            |
		// After discount  |  40.4819           |  34.0184              | 6.4635

		// 4.1. Get VAT before discount
		$this->assertEquals(7.1816, $cart->getVATBeforeDiscount(), '', 0.01);

		// 4.2. Total before discount without VAT
		$this->assertEquals(37.7983, $cart->getTotalBeforeDiscountWithoutVAT(), '', 0.01);

		// 4.3. Total after discount without VAT
		$this->assertEquals(34.0184, $cart->getTotalAfterDiscountWithoutVAT(), '', 0.01);

		// 4.4. VAT after discount
		$this->assertEquals(6.4635, $cart->getVATAfterDiscount(), '', 0.01);

		// 5. Check total after discount is correct
		$this->assertEquals(40.4819, $cart->getTotalAfterDiscount(),'', 0.01);

		// 6. Check global total is correct
		$this->assertEquals(50.4819, $cart->getTotal(),'', 0.01);

		// 7. Check VAT
		$this->assertEquals(6.4635, $cart->getVAT(), '', 0.01);
	}

    public function testCartGetProducts_AllAvailable()
    {
        $cart_srl = 774;
        $cart = new Cart($cart_srl);

        $this->assertEquals(2, count($cart->getProducts()));
    }

    public function testCartGetProducts_AllAvailableWithLimit()
    {
        $cart_srl = 774;
        $cart = new Cart($cart_srl);

        $this->assertEquals(1, count($cart->getProducts(1)));
    }

    public function testCartGetProducts_Unavailable()
    {
        $cart_srl = 774;
        $deleted_product_srl = 133;
        $cart = new Cart($cart_srl);

        // Act: delete one product from xe_products but keep it in cart
        $product_repository = new ProductRepository();
        $product_repository->deleteProduct(array('product_srl'=>$deleted_product_srl));

        // If we activate onlyAvailable, only 1 product should be returned
        $this->assertEquals(1, count($cart->getProducts(NULL, TRUE)));
        // Default, onlyAvailable is false, so all products should be returned => 2
        $this->assertEquals(2, count($cart->getProducts()));
    }

    /**
     * Test cart when a product becomes unavailable (deleted / out of stock)
     * after the user has already added it to the cart
     */
    public function testCartTotal_WithUnavailableProducts()
    {
        $module_srl = 107;
        $cart_srl = 774;
        $deleted_product_srl = 133;

        // Act: delete one product from xe_products but keep it in cart
        $product_repository = new ProductRepository();
        $args = new stdClass();
        $args->product_srl = $deleted_product_srl;
        $product_repository->deleteProduct($args);

        $cart = new Cart($cart_srl);

        // Assert
        // 1. Check that cart has expected products
        $this->assertEquals(1, count($cart->getProducts(NULL, TRUE))); // When $onlyAvailable is true, count just availablel products
        $this->assertEquals(2, count($cart->getProducts())); // Default, , show all products

        // 3. Check that item total is correct
        $this->assertEquals(29.99, $cart->getItemTotal()); // Count just available products

        // 4. Check global total is correct (includes shipping +10)
        $this->assertEquals(39.99, $cart->getTotal(), '', 0.01); // Count just available products
    }

	public function testDiscountPercentageBeforeVAT()
	{
		$discount = new PercentageDiscount(100, 40, 99, 20, TRUE);

		$this->assertEquals(83.3333, $discount->getValueWithoutVAT(), 'Wrong VAT', 0.01);
		$this->assertEquals(33.3333, $discount->getReductionValue(), 'Wrong reduction at discount before VAT', 0.01);
		$this->assertEquals($discount->getTotalValue() - $discount->getReductionValue(), $discount->getValueDiscounted(), 'Wrong discounted value');
	}

	public function testDiscountPercentageAfterVAT()
	{
		$discount = new PercentageDiscount(100, 40, 99, 20, FALSE);

		$this->assertEquals(40, $discount->getReductionValue(), 'Wrong reduction at discount after VAT');
		$this->assertEquals($discount->getTotalValue() - $discount->getReductionValue(),$discount->getValueDiscounted(), 'Wrong reduction discounted value');
	}

	public function testDiscountFixedAmount()
	{
		$discount = new FixedAmountDiscount(1000, 200, 999, 20, TRUE);

		$discount->setCalculateBeforeVAT(TRUE);
		$this->assertEquals(200, $discount->getReductionValue());
		$this->assertEquals(800, $discount->getValueDiscounted());

		$discount->setCalculateBeforeVAT(FALSE);
		$this->assertEquals(200, $discount->getReductionValue());
		$this->assertEquals(800, $discount->getValueDiscounted());
	}

	/**
	 * Test that an empty cart has 0 products
	 */
	public function testEmptyCart()
	{
		$cart = new Cart();
		$cart->module_srl = 123;
		$cart->save();

		$this->assertEquals(0, count($cart->getProducts()));
	}

    public function testDeleteProduct()
    {
        $cart = new Cart(774);
        $cart->removeProducts(array(133));
        $this->assertEquals(1, $cart->count(), "Count failed after delete");
        $this->assertFalse($cart->hasProduct(133), 'Cart_product still exists after delete');
    }

    public function testAddProduct()
    {
        $cart = new Cart(774);
        $pRepo = new ProductRepository();
        $product1 = $pRepo->getProduct(130);
        $product2 = $pRepo->getProduct(133);
        $cart->emptyCart();
        $this->assertEquals(0, $cart->count(), "Delete didn't work");
        $cart->addProduct($product1);
        $this->assertEquals(1, $cart->count(), "Adding product 1 failed");
        $this->assertEquals($cart->count(), count($cart->getProducts()), "?");
        $cart->addProduct($product2, 1405);
        $this->assertEquals(2, $cart->count(), "Adding product 2 failed");
        //getProducts should ignore its internal cache, so we tell it to.
        $this->assertEquals($cart->count(), count($cart->getProducts(NULL, NULL, TRUE)), "?");
    }

    public function testCartChange()
    {
        $cRepo = new CartRepository();
        $pRepo = new ProductRepository();
        $anonCart = $cRepo->getCart(107, NULL, NULL, 'anonSession');
        $userCart = $cRepo->getCart(107, NULL, 4);
        $userCart->emptyCart();
        $anonCart->emptyCart();
        $products = array(
            1 => $pRepo->getProduct(130),
            2 => $pRepo->getProduct(133),
            3 => $pRepo->getProduct(132)
        );
        $userCart->addProduct($products[1], 2);
        $anonCart->addProduct($products[1], 13);
        $userCart->merge($anonCart, FALSE);
        $this->assertEquals(15, $userCart->getCartProduct(130)->quantity, "Wrong quantity");
    }

    public function testCount()
    {
        $cRepo = new CartRepository();
        $pRepo = new ProductRepository();
        $cart = $cRepo->getCart(NULL, 14);
        $cart->emptyCart();
        $cart->addProduct($pRepo->getProduct(130));
        $cart->addProduct($pRepo->getProduct(132));
        $this->assertEquals(2, $cart->count());
    }

}