<?php
require_once dirname(__FILE__) . "/../lib/Shop_Generic_Tests.class.php";
require_once dirname(__FILE__) . '/../lib/Bootstrap.php';
require_once dirname(__FILE__) . '/../../libs/repositories/AttributeRepository.php';

class AttributeTest extends Shop_Generic_Tests_DatabaseTestCase
{
    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_attributes' => array(
                array(
                    'attribute_srl' => 1405,
                    'member_srl'    => 7,
					'module_srl'	=> 13,
                    'title'         => 'hehe',
                    'type'          => 'select',
                    'required'      => 'Y',
                    'status'        => 'Y',
                    'values'        => 'sa|b|cas',
                    'default_value' => 'sa',
                    'regdate'       => '20100424171523',
                    'last_update'   => '20100424191523'
                ),
                array(
                    'attribute_srl' => 1406,
                    'member_srl'    => 7,
					'module_srl'	=> 13,
                    'title'         => 'trolo',
                    'type'          => 'select',
                    'required'      => 'Y',
                    'status'        => 'Y',
                    'values'        => 'be|he|he2',
                    'default_value' => 'he',
                    'regdate'       => '20100424121513',
                    'last_update'   => '20100424141412'
                ),
				array(
					'attribute_srl' => 1407,
					'member_srl'    => 7,
					'module_srl'	=> 14,
					'title'         => 'hehe',
					'type'          => 'select',
					'required'      => 'Y',
					'status'        => 'Y',
					'values'        => 'sa|b|cas',
					'default_value' => 'sa',
					'regdate'       => '20100424171523',
					'last_update'   => '20100424191523'
				),
				array(
					'attribute_srl' => 1408,
					'member_srl'    => 7,
					'module_srl'	=> 14,
					'title'         => 'trolo',
					'type'          => 'select',
					'required'      => 'Y',
					'status'        => 'Y',
					'values'        => 'be|he|he2',
					'default_value' => 'he',
					'regdate'       => '20100424121513',
					'last_update'   => '20100424141412'
				)
            ),
			'xe_shop_attributes_scope' => array(
				array('attribute_srl' => 1407, 'category_srl' => 100),
				array('attribute_srl' => 1407, 'category_srl' => 101),
				array('attribute_srl' => 1407, 'category_srl' => 102),
				array('attribute_srl' => 1408, 'category_srl' => 101)
			)
        ));
    }

    public function testAddEntry()
    {
        $this->assertEquals(4, $this->getConnection()->getRowCount('xe_shop_attributes'), "First count");

		/**
		 * @var shopModel $model
		 */
		$model = getModel('shop');
        $model = $model->getAttributeRepository();
        $attribute = new Attribute((object) array(
            'member_srl'    => 7,
			'module_srl'	=> 9,
            'title'         => 'yoyo',
            'type'          => 'select',
            'required'      => 'Y',
            'status'        => 'Y',
            'values'        => 'a|b|c',
            'default_value' => 'c',
            'regdate'       => '20100424121513',
            'last_update'   => '20100424141412'
        ));
		$model->insertAttribute($attribute);
        $this->assertEquals(5, $this->getConnection()->getRowCount('xe_shop_attributes'), "Insert failed");
    }

    public function testAddEntryWithSpaces()
    {
        $this->assertEquals(4, $this->getConnection()->getRowCount('xe_shop_attributes'), "First count");

        /**
         * @var shopModel $model
         */
        $model = getModel('shop');
        $attribute_repository = $model->getAttributeRepository();
        $attribute = new Attribute((object) array(
            'member_srl'    => 7,
            'module_srl'	=> 9,
            'title'         => 'yoyo',
            'type'          => 'select',
            'required'      => 'Y',
            'status'        => 'Y',
            'values'        => 'a | b | c ',
            'default_value' => 'c',
            'regdate'       => '20100424121513',
            'last_update'   => '20100424141412'
        ));
        $attribute_repository->insertAttribute($attribute);
        $this->assertEquals(5, $this->getConnection()->getRowCount('xe_shop_attributes'), "Insert failed");

        $attributes = $attribute_repository->getAttributes(array());
        $attribute = NULL;
        foreach($attributes as $attribute)
        {
            if(!in_array($attribute->attribute_srl, array(1405, 1406, 1407, 1408)))
            {
                break;
            }
        }

        $this->assertEquals('a|b|c', implode('|', $attribute->values));

        $attribute->values = 'a   |b    |c';
        $attribute_repository->updateAttribute($attribute);
        $attribute = array_shift($attribute_repository->getAttributes(array($attribute->attribute_srl)));

        $this->assertEquals('a|b|c', implode('|', $attribute->values));

    }

	/**
	 * Test that configurable attributes are properly stored in the product object
	 */
	public function testConfigurableProductsAreStoredAsAssociativeArray()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$product_repository = $shopModel->getProductRepository();

		$args = new stdClass();
		$args->module_srl = 112;
		$args->member_srl = 22;
		$args->title = "Some product";
		$args->sku = 'some-product';
		$args->price = 22;
		$args->configurable_attributes = array(1405, 1406);

		$configurable_product = new ConfigurableProduct($args);

		$this->assertEquals(2, count($configurable_product->configurable_attributes));

		foreach($configurable_product->configurable_attributes as $attribute_srl => $attribute_title)
		{
			$this->assertTrue(in_array($attribute_srl, array(1405, 1406)));
		}

	}

	/**
	 * Tests adding configurable attributes
	 */
	public function testAddConfigurableAttributes()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$product_repository = $shopModel->getProductRepository();

		$args = new stdClass();
		$args->module_srl = 112;
		$args->member_srl = 22;
		$args->title = "Some product";
		$args->sku = 'some-product';
		$args->price = 22;
		$args->configurable_attributes = array(1405, 1406);

		$configurable_product = new ConfigurableProduct($args);
		$new_product_srl = $product_repository->insertProduct($configurable_product);

		/**
		 * @var ConfigurableProduct $new_product
		 */
		$new_product = $product_repository->getProduct($new_product_srl);

		$this->assertEquals(2, count($new_product->configurable_attributes));
		foreach($new_product->configurable_attributes as $attribute_srl => $attribute_title)
		{
			$this->assertTrue(in_array($attribute_srl, array(1405, 1406)));
			$this->assertTrue(in_array($attribute_title, array("hehe", "trolo")));
		}
	}

	/**
	 * Test adding attributes for associated products
	 */
	public function testAddAssociatedProductsAttributes()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$product_repository = $shopModel->getProductRepository();

		// Create a parent product
		$args = new stdClass();
		$args->module_srl = 112;
		$args->member_srl = 22;
		$args->title = "Some product";
		$args->sku = 'some-product';
		$args->price = 22;
		$args->configurable_attributes = array(1405, 1406);

		$configurable_product = new ConfigurableProduct($args);
		$configurable_product_srl = $product_repository->insertProduct($configurable_product);

		// Create an associated product
		$attribute_values = array("sa", "he");
		$associated_product = $product_repository->createProductFromParent($configurable_product, $attribute_values);

		// Insert associated product
		$product_srl = $product_repository->insertProduct($associated_product);

		// Check that attributes were successfully saved and loaded
		$new_associated_product = $product_repository->getProduct($product_srl);
		$this->assertEquals(2, count($new_associated_product->attributes));
		$this->assertEquals("sa", $new_associated_product->attributes[1405]);
		$this->assertEquals("he", $new_associated_product->attributes[1406]);

		// Check that parent product configurable attributes are still there
		$new_configurable_product = $product_repository->getProduct($configurable_product_srl);
		$this->assertEquals(2, count($new_configurable_product ->configurable_attributes));
		foreach($new_configurable_product ->configurable_attributes as $attribute_srl => $attribute_title)
		{
			$this->assertTrue(in_array($attribute_srl, array(1405, 1406)));
			$this->assertTrue(in_array($attribute_title, array("hehe", "trolo")));
		}
	}


	/**
	 * Test adding attributes for associated products
	 */
	public function testAddAssociatedProductsAttributesWhenAttributeNotInScope()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$product_repository = $shopModel->getProductRepository();

		// Create a parent product
		$args = new stdClass();
		$args->module_srl = 14;
		$args->member_srl = 22;
		$args->title = "Some product";
		$args->sku = 'some-product';
		$args->price = 22;
		$args->configurable_attributes = array(1407, 1408);
		$args->categories = array(100);

		$configurable_product = new ConfigurableProduct($args);
		$configurable_product_srl = $product_repository->insertProduct($configurable_product);

		// Create an associated product
		$attribute_values = array("sa", "he");
		$associated_product = $product_repository->createProductFromParent($configurable_product, $attribute_values);

		// Insert associated product
		$product_srl = $product_repository->insertProduct($associated_product);

		// Check that attributes were successfully saved and loaded
        // Aka - both attributes where saved even though one is not in scope
        // This is a special case for associated products
		$new_associated_product = $product_repository->getProduct($product_srl);
		$this->assertEquals(2, count($new_associated_product->attributes));
		$this->assertEquals("sa", $new_associated_product->attributes[1407]);
        $this->assertEquals("he", $new_associated_product->attributes[1408]);

		// Check that parent product configurable attributes are still there
		$new_configurable_product = $product_repository->getProduct($configurable_product_srl);
		$this->assertEquals(2, count($new_configurable_product ->configurable_attributes));
		foreach($new_configurable_product ->configurable_attributes as $attribute_srl => $attribute_title)
		{
			$this->assertTrue(in_array($attribute_srl, array(1407, 1408)));
			$this->assertTrue(in_array($attribute_title, array("hehe", "trolo")));
		}
	}

}