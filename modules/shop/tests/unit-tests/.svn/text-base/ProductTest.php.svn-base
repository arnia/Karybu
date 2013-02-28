<?php

require_once dirname(__FILE__) . "/../lib/Shop_Generic_Tests.class.php";
require_once dirname(__FILE__) . '/../lib/Bootstrap.php';

require_once dirname(__FILE__) . '/../../libs/repositories/ProductRepository.php';

/**
 *  Test features related to Products
 *  @author Corina Udrescu (dev@xpressengine.org)
 */
class ProductTest extends Shop_Generic_Tests_DatabaseTestCase
{
	const PRODUCT = 1,
					CATEGORY_BOOKS = 265,
					CATEGORY_PHOTOGRAPHY = 266,
					CATEGORY_ARCHITECTURE = 267,
					CATEGORY_PROGRAMMING = 268,
					CATEGORY_TSHIRTS = 276,
					ATTRIBUTE_URL = 253,
					ATTRIBUTE_COLOR = 278,
					ATTRIBUTE_SIZE = 274,
					ATTRIBUTE_PUBLISH_YEAR = 273,
					ATTRIBUTE_AUTHOR = 279
					;

	/**
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet()
	{
		return new Shop_DbUnit_ArrayDataSet(array(
			'xe_shop_products' => array(
				array(
					'product_srl' => self::PRODUCT,
					'member_srl' => 4,
					'module_srl' => 10,
					'parent_product_srl' => 0,
					'product_type' => 'simple',
					'title' => 'carte',
					'description' => 'povestiri',
					'short_description' => 'povestiri',
					'sku' => 'carte',
					'weight' => 100,
					'status' => 'enabled',
					'friendly_url' => 'carte',
					'price' => 200,
					'qty' => 10,
					'in_stock' => 'Y',
					'related_products' => NULL,
					'regdate' => date("YmdGis"),
					'last_update' => date("YmdGis")
				),
				array('product_srl' => 302, 'member_srl' => 4, 'module_srl' => 104, 'parent_product_srl' => 297, 'product_type' => 'simple', 'title' => 'Tricou_M_alb', 'description' => NULL, 'short_description' => NULL, 'sku' => 'tricou_M_alb', 'weight' => NULL, 'status' => NULL, 'friendly_url' => NULL, 'price' => 100, 'qty' => NULL, 'in_stock' => 'N', 'related_products' => NULL, 'regdate' => 20120809193142, 'last_update' => 20120809193142),
				array('product_srl' => 301, 'member_srl' => 4, 'module_srl' => 104, 'parent_product_srl' => 297, 'product_type' => 'simple', 'title' => 'Tricou_M_rosu', 'description' => NULL, 'short_description' => NULL, 'sku' => 'tricou_M_rosu', 'weight' => NULL, 'status' => NULL, 'friendly_url' => NULL, 'price' => 100, 'qty' => NULL, 'in_stock' => 'N', 'related_products' => NULL, 'regdate' => 20120809193142, 'last_update' => 20120809193142),
				array('product_srl' => 300, 'member_srl' => 4, 'module_srl' => 104, 'parent_product_srl' => 297, 'product_type' => 'simple', 'title' => 'Tricou_S_negru', 'description' => NULL, 'short_description' => NULL, 'sku' => 'tricou_S_negru', 'weight' => NULL, 'status' => NULL, 'friendly_url' => NULL, 'price' => 100, 'qty' => NULL, 'in_stock' => 'N', 'related_products' => NULL, 'regdate' => 20120809193142, 'last_update' => 20120809193142),
				array('product_srl' => 299, 'member_srl' => 4, 'module_srl' => 104, 'parent_product_srl' => 297, 'product_type' => 'simple', 'title' => 'Tricou_S_alb', 'description' => NULL, 'short_description' => NULL, 'sku' => 'tricou_S_alb', 'weight' => NULL, 'status' => NULL, 'friendly_url' => NULL, 'price' => 100, 'qty' => NULL, 'in_stock' => 'N', 'related_products' => NULL, 'regdate' => 20120809193142, 'last_update' => 20120809193142),
				array('product_srl' => 304, 'member_srl' => 4, 'module_srl' => 104, 'parent_product_srl' => NULL, 'product_type' => 'simple', 'title' => 'O carte', 'description' => 'fewhjgjg', 'short_description' => 'O carte', 'sku' => 'o-carte', 'weight' => 999, 'status' => 'enabled', 'friendly_url' => 'o-carte', 'price' => 999, 'qty' => 99, 'in_stock' => 'Y', 'related_products' => NULL, 'regdate' => 20120813140441, 'last_update' => 20120813152057),
				array('product_srl' => 298, 'member_srl' => 4, 'module_srl' => 104, 'parent_product_srl' => 297, 'product_type' => 'simple', 'title' => 'Tricou_S_rosu', 'description' => NULL, 'short_description' => NULL, 'sku' => 'tricou_S_rosu', 'weight' => NULL, 'status' => NULL, 'friendly_url' => NULL, 'price' => 100, 'qty' => NULL, 'in_stock' => 'N', 'related_products' => NULL, 'regdate' => 20120809193142, 'last_update' => 20120809193142),
				array('product_srl' => 297, 'member_srl' => 4, 'module_srl' => 104, 'parent_product_srl' => NULL, 'product_type' => 'configurable', 'title' => 'Tricou', 'description' => 'Bumbac 100%.', 'short_description' => 'Tricou', 'sku' => 'tricou', 'weight' => 50, 'status' => 'enabled', 'friendly_url' => 'tricou', 'price' => 100, 'qty' => NULL, 'in_stock' => 'Y', 'related_products' => NULL, 'regdate' => 20120809193134, 'last_update' => 20120813152109),
				array('product_srl' => 303, 'member_srl' => 4, 'module_srl' => 104, 'parent_product_srl' => 297, 'product_type' => 'simple', 'title' => 'Tricou_M_negru', 'description' => NULL, 'short_description' => NULL, 'sku' => 'tricou_M_negru', 'weight' => NULL, 'status' => NULL, 'friendly_url' => NULL, 'price' => 100, 'qty' => NULL, 'in_stock' => 'N', 'related_products' => NULL, 'regdate' => 20120809193142, 'last_update' => 20120809193142)
			),
			'xe_shop_categories' => array(
				array('category_srl' => self::CATEGORY_BOOKS, 'module_srl' => 104, 'parent_srl' => 0,   'filename' => NULL, 'title' => 'Carti', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185846, 'last_update' => 20120807185846),
				array('category_srl' => self::CATEGORY_PHOTOGRAPHY, 'module_srl' => 104, 'parent_srl' => self::CATEGORY_BOOKS, 'filename' => NULL, 'title' => 'Fotografie', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185857, 'last_update' => 20120807185857),
				array('category_srl' => self::CATEGORY_ARCHITECTURE, 'module_srl' => 104, 'parent_srl' => self::CATEGORY_BOOKS, 'filename' => NULL, 'title' => 'Arhitectura', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185902, 'last_update' => 20120807185902),
				array('category_srl' => self::CATEGORY_PROGRAMMING, 'module_srl' => 104, 'parent_srl' => self::CATEGORY_BOOKS, 'filename' => NULL, 'title' => 'Programare', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185908, 'last_update' => 20120807185908),
				array('category_srl' => self::CATEGORY_TSHIRTS, 'module_srl' => 104, 'parent_srl' => 0, 'filename' => NULL, 'title' => 'Tricouri', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807190353, 'last_update' => 20120807190353)
			),
			'xe_shop_product_categories' => array(
				array('product_srl' => self::PRODUCT, 'category_srl' => self::CATEGORY_BOOKS),
				array('product_srl' => self::PRODUCT, 'category_srl' => self::CATEGORY_PHOTOGRAPHY),
				array('product_srl' => 297, 'category_srl' => self::CATEGORY_TSHIRTS),
				array('product_srl' => 304, 'category_srl' => self::CATEGORY_BOOKS),
				array('product_srl' => 304, 'category_srl' => self::CATEGORY_PHOTOGRAPHY),
				array('product_srl' => 298, 'category_srl' => self::CATEGORY_TSHIRTS),
				array('product_srl' => 299, 'category_srl' => self::CATEGORY_TSHIRTS),
				array('product_srl' => 300, 'category_srl' => self::CATEGORY_TSHIRTS),
				array('product_srl' => 301, 'category_srl' => self::CATEGORY_TSHIRTS),
				array('product_srl' => 302, 'category_srl' => self::CATEGORY_TSHIRTS),
				array('product_srl' => 303, 'category_srl' => self::CATEGORY_TSHIRTS)
			),
			'xe_shop_attributes'  => array(
				array('attribute_srl' => self::ATTRIBUTE_URL, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'URL', 'type' => 1, 'required' => 'Y', 'status' => 'Y', 'default_value' => NULL, 'values' => NULL, 'regdate' => 20120807160414, 'last_update' => 20120807160414),
				array('attribute_srl' => self::ATTRIBUTE_COLOR, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'Culoare', 'type' => 1, 'required' => 'Y', 'status' => 'Y', 'default_value' => NULL, 'values' => NULL, 'regdate' => 20120808110402, 'last_update' => 20120808110402),
				array('attribute_srl' => self::ATTRIBUTE_SIZE, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'Marime', 'type' => 5, 'required' => 'Y', 'status' => 'Y', 'default_value' => 'M', 'value' => 'S|M|L', 'regdate' => 20120807190419, 'last_update' => 20120807190419),
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'Anul aparitiei', 'type' => 1, 'required' => 'N', 'status' => 'Y', 'default_value' => NULL, 'values' => NULL, 'regdate' => 20120807190150, 'last_update' => 20120807190150),
				array('attribute_srl' => self::ATTRIBUTE_AUTHOR, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'Autor', 'type' => 1, 'required' => 'Y', 'status' => 'Y', 'default_value' => NULL, 'values' => NULL, 'regdate' => 20120808110540, 'last_update' => 20120808110540)
			),
			'xe_shop_attributes_scope' => array(
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'category_srl' => self::CATEGORY_BOOKS),
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'category_srl' => self::CATEGORY_PHOTOGRAPHY),
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'category_srl' => self::CATEGORY_ARCHITECTURE),
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'category_srl' => self::CATEGORY_PROGRAMMING),
				array('attribute_srl' => self::ATTRIBUTE_AUTHOR, 'category_srl' => self::CATEGORY_PHOTOGRAPHY),
				array('attribute_srl' => self::ATTRIBUTE_COLOR, 'category_srl' => self::CATEGORY_TSHIRTS),
				array('attribute_srl' => self::ATTRIBUTE_SIZE, 'category_srl' => self::CATEGORY_TSHIRTS)
			),
			'xe_shop_product_attributes' => array(
				array('product_srl' => 304, 'attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'value' => '2002'),
				array('product_srl' => 304, 'attribute_srl' => self::ATTRIBUTE_AUTHOR, 'value' => 'lalala'),
				array('product_srl' => 298, 'attribute_srl' => self::ATTRIBUTE_URL, 'value' => 'some-product')
			)
		));
	}


	/**
	 * Tests inserting a new Product - makes sure all fields are properly persisted
	 * @author Dan dragan (dev@xpressengine.org)
	 */
	public function testInsertProduct_ValidData()
	{
		// Create new Product object
		$args = new SimpleProduct();
		$args->module_srl = 201;
		$args->member_srl = 4;
		$args->product_type = "simple";
		$args->title = "Product 1";
		$args->description = "Lorem ipsum dolor sit amet, te amet scaevola volutpat eum, ius an decore recteque patrioque, mel sint epicurei ut. Ea lorem noluisse est, ea sed nisl libris electram. Cu vivendum facilisis scribentur mel, bonorum elaboraret no per. Nec eu vidit omittantur, ei putant timeam detraxit quo, urbanitas efficiendi sit id. Mei putent eirmod voluptua ut, at dictas invenire delicata duo.

Patrioque conceptam in mea. Est ad ullum ceteros, pro quem accumsan appareat id, pro nominati electram no. Duo lorem maiorum urbanitas te, cu eum dicunt laoreet, etiam sententiae scriptorem at mel. Vix tamquam epicurei et, quo tota iudicabit an. Duo ea agam antiopam. Et per diam percipitur.";
		$args->short_description = "short_description";
		$args->sku = "SKU1";
		$args->status = '1';
		$args->friendly_url = "product1";
		$args->price = 10.5;
		$args->qty = 0;
		$args->in_stock = "Y";

		$shopModel = getModel('shop');
		$repository = $shopModel->getProductRepository();
		try
		{
			// Try to insert the new Product

			$product_srl = $repository->insertProduct($args);

			// Check that a srl was returned
			$this->assertNotNull($product_srl);

			// Read the newly created object from the database, to compare it with the source object
			$output = Database::executeQuery("SELECT * FROM xe_shop_products WHERE product_srl = $product_srl");
			$this->assertEquals(1, count($output));

			$product = $output[0];
			$this->assertEquals($args->module_srl, $product->module_srl);
			$this->assertEquals($args->member_srl, $product->member_srl);
			$this->assertEquals($args->product_type, $product->product_type);
			$this->assertEquals($args->title, $product->title);
			$this->assertEquals($args->description, $product->description);
			$this->assertEquals($args->short_description, $product->short_description);
			$this->assertEquals($args->sku, $product->sku);
			$this->assertEquals($args->status, $product->status);
			$this->assertEquals($args->friendly_url, $product->friendly_url);
			$this->assertEquals($args->price, $product->price);
			$this->assertEquals($args->qty, $product->qty);
			$this->assertEquals($args->in_stock, $product->in_stock);
			$this->assertNotNull($product->regdate);
			$this->assertNotNull($product->last_update);
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}


	/**
	 * Test inserting a product attribute
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testInsertProductAttribute()
	{
		$repository = new ProductRepository();
		$product = $repository->getProduct(1);

		// Update a global attribute, just to check that update works
		// Global = applies to all categories
		$product->attributes[self::ATTRIBUTE_URL] = "some-slug";
		$repository->updateProduct($product);

		$new_product = $repository->getProduct(1);

		$this->assertEquals(1, count($new_product->attributes));
		$this->assertEquals("some-slug", $new_product->attributes[self::ATTRIBUTE_URL]);
	}

	/**
	 * Test inserting product attributes
	 *
	 * Each category has associated a set of attributes
	 * (e.g. All books have the Author attribute, All T-Shirts have Color)
	 * Based on the category a product is in, it can have values for these attributes.
	 *
	 * This test checks that if Color is provided for a product in the Books
	 * category it will not be added.
	 *
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testInsertProductAttributeSkipsAttributesNotInScope()
	{
		$repository = new ProductRepository();
		$product = $repository->getProduct(1);

		$product->attributes[self::ATTRIBUTE_AUTHOR] = "J. K. Rowling";
		$product->attributes[self::ATTRIBUTE_PUBLISH_YEAR] = 2003;
		$product->attributes[self::ATTRIBUTE_COLOR] = "Blue";

		$repository->updateProduct($product);

		$new_product = $repository->getProduct(1);

		$this->assertEquals(2, count($new_product->attributes));

		$this->assertFalse(array_key_exists(self::ATTRIBUTE_COLOR, $new_product->attributes));
		$this->assertEquals("J. K. Rowling", $new_product->attributes[self::ATTRIBUTE_AUTHOR]);
		$this->assertEquals(2003, $new_product->attributes[self::ATTRIBUTE_PUBLISH_YEAR]);
	}

	/**
	 * Test get all products
	 *
	 * Method should return just products without parent_srl
	 * Simple products should be SimpleProduct instances
	 * Configurable products should be ConfigurableProduct instances with associated_products
	 *
	 * @return void
	 */
	public function testGetAllProducts()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');

		$product_repository = $shopModel->getProductRepository();

		$args = new stdClass();
		$args->module_srl = 104;
		$output = $product_repository->getProductList($args);

		$products = $output->products;
		$this->assertEquals(2, count($products));

		foreach($products as $product)
		{
            $this->assertNull($product->parent_product_srl);

			if($product->isConfigurable())
			{
				$this->assertTrue(is_a($product, 'ConfigurableProduct'));
				$this->assertEquals(6, count($product->associated_products));

				foreach($product->associated_products as $associated_product)
				{
					$this->assertTrue(is_a($associated_product, 'SimpleProduct'));
				}
			}
			elseif($product->isSimple())
			{
				$this->assertTrue(is_a($product, 'SimpleProduct'));
			}
		}
	}

	/**
	 * Test get all products by category
	 */
	public function testGetProductsListByCategory()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');

		$product_repository = $shopModel->getProductRepository();

		$args = new stdClass();
		$args->module_srl = 104;
		$args->category_srls = array(self::CATEGORY_TSHIRTS);
		$output = $product_repository->getProductList($args);

		$products = $output->products;
		$this->assertEquals(1, count($products));

		foreach($products as $product)
		{
			$this->assertNull($product->parent_product_srl);

			$this->assertTrue($product->isConfigurable());
			$this->assertTrue(is_a($product, 'ConfigurableProduct'));

			$this->assertEquals(6, count($product->associated_products));

			foreach($product->associated_products as $associated_product)
			{
				$this->assertTrue(is_a($associated_product, 'SimpleProduct'));
			}
		}

	}

	/**
	 * Tests that when a product is deleted, its attributes are also deleted
	 */
	public function testDeletingProductDeletesAttributes()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$product_repository = $shopModel->getProductRepository();

		// Delete product
		$args = new stdClass();
		$args->product_srl = 304;
		$product_repository->deleteProduct($args);

		// Check that product was deleted
		$this->assertNull($product_repository->getProduct(304));

		/**
		 * @var PHPUnit_Extensions_Database_DataSet_QueryTable $queryTable
		 */
		$queryTable = $this->getConnection()->createQueryTable(
			'xe_shop_product_attributes', 'SELECT * FROM xe_shop_product_attributes'
		);

		$row_count = $queryTable->getRowCount();
		// Check that attributes for other products were not deleted.
		$this->assertNotEquals(0, $row_count);

		for($i = 0; $i < $row_count; $i++)
		{
			$row = $queryTable->getRow($i);
			// Check that attributes were deleted
			$this->assertNotEquals(304, $row->product_srl);
		}
	}

	public function testDeleteProduct(){
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$product_repository = $shopModel->getProductRepository();

		// Delete product
		$args = new stdClass();
		$args->product_srl = 297;
		$args->product_type = 'configurable';
		$product_repository->deleteProduct($args);

		// Check that product was deleted
		$this->assertNull($product_repository->getProduct(297));

		// Check that associated product was deleted
		$this->assertNull($product_repository->getProduct(298));
		$this->assertNull($product_repository->getProduct(299));
		$this->assertNull($product_repository->getProduct(300));
		$this->assertNull($product_repository->getProduct(301));
		$this->assertNull($product_repository->getProduct(302));
		$this->assertNull($product_repository->getProduct(303));

		// Check that other products are not deleted
		$other_product = $product_repository->getProduct(304);
		$this->assertNotNull($product_repository->getProduct(304));
	}

}