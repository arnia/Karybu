<?php

require_once dirname(__FILE__) . '/../lib/Bootstrap.php';
require_once dirname(__FILE__) . "/../lib/Shop_Generic_Tests.class.php";

require_once dirname(__FILE__) . '/../../libs/model/Category.php';

/**
 *  Test features related to Product categories hierarchical tree display and sorting
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class CategoryTreeSortingTest extends Shop_Generic_Tests_DatabaseTestCase
{

    const CATEGORY_PHONES = 1,
        CATEGORY_SAMSUNG = 2,
        CATEGORY_NOKIA = 3,
        CATEGORY_LG = 4,
        CATEGORY_MASERATI = 5,
        CATEGORY_LAPTOPS = 6,
        CATEGORY_APPLE = 7,
        CATEGORY_FUJITSU = 8,
        CATEGORY_CARS = 9,
        CATEGORY_SONY_ERICSSON = 10;

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_categories' => array(
                array('category_srl' => self::CATEGORY_PHONES,'module_srl' => '107','parent_srl' => 0,'filename' => NULL,'title' => 'Phones','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184414','last_update' => '20120924184414','list_order' => self::CATEGORY_PHONES),
                array('category_srl' => self::CATEGORY_SAMSUNG,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'Samsung','description' => 'sdfsadfsadfsa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120921192939','last_update' => '20120924180718','list_order' => self::CATEGORY_SAMSUNG),
                array('category_srl' => self::CATEGORY_NOKIA,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'Nokia','description' => 'sdfdasf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183752','last_update' => '20120924180728','list_order' => self::CATEGORY_NOKIA),
                array('category_srl' => self::CATEGORY_LG,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'LG','description' => 'sdfsaf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183758','last_update' => '20120924180737','list_order' => self::CATEGORY_LG),
//                array('category_srl' => self::CATEGORY_MASERATI,'module_srl' => '107','parent_srl' => self::CATEGORY_CARS,'filename' => NULL,'title' => 'Maserati','description' => 'aaaa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183804','last_update' => '20120924180750','list_order' => self::CATEGORY_MASERATI),
//                array('category_srl' => self::CATEGORY_LAPTOPS,'module_srl' => '107','parent_srl' => 0,'filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bad977.','title' => 'Laptops','description' => 'descriere laptops','product_count' => '6','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','list_order' => self::CATEGORY_LAPTOPS),
//                array('category_srl' => self::CATEGORY_APPLE,'module_srl' => '107','parent_srl' => self::CATEGORY_LAPTOPS,'filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bafb2a.','title' => 'Apple','description' => '','product_count' => '0','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120924180743','list_order' => self::CATEGORY_APPLE),
//                array('category_srl' => self::CATEGORY_FUJITSU,'module_srl' => '107','parent_srl' => self::CATEGORY_APPLE,'filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bb0f06.','title' => 'Fujitsu','description' => '','product_count' => '5','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','list_order' => self::CATEGORY_FUJITSU),
//                array('category_srl' => self::CATEGORY_CARS,'module_srl' => '107','parent_srl' => 0,'filename' => NULL,'title' => 'Cars','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184441','last_update' => '20120924184441','list_order' => self::CATEGORY_CARS),
//                array('category_srl' => self::CATEGORY_SONY_ERICSSON,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'Sony Ericsson','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924185725','last_update' => '20120924185725','list_order' => self::CATEGORY_SONY_ERICSSON),
            ),
            'xe_shop_products' => array(),
            'xe_shop_product_categories' => array()
        ));
    }

    /**
     * Move any node under parent 0 and as the first in list
     *
     * Input from client side tree:
     *  parent_srl = 0
     *  target_srl = 0
     */
    public function testMoveNodeAsFirstInListRootLevel()
    {
        $category_repository = new CategoryRepository();
        // At first, the tree is like this:
        // 1. Phones [1]
        //    2. Samsung [2]
        //    3. Nokia [3]
        // We'll move Nokia at the top of the tree
        $category_repository->moveCategory(self::CATEGORY_NOKIA, 0, 0);

        // We expect the following tree:
        // 1. Nokia [1]
        // 2. Phones [2]
        //    3. Samsung [2]
        $tree = $category_repository->getCategoriesTree(107);
        $this->assertNotNull($tree);


        $nokia = array_shift($tree->children);
        $phones = array_shift($tree->children);
        $samsung = array_shift($phones->children);

        $this->assertEquals(self::CATEGORY_NOKIA, $nokia->category->category_srl);
        $this->assertEquals(1, $nokia->category->list_order);

        $this->assertEquals(self::CATEGORY_PHONES, $phones->category->category_srl);
        $this->assertEquals(2, $phones->category->list_order);

        $this->assertEquals(self::CATEGORY_SAMSUNG, $samsung->category->category_srl);
        $this->assertEquals(2, $samsung->category->list_order);
    }


    /**
     * Move any node under another one that doesn't have children
     * thus creating a new subcategory
     *
     * Input from client side tree:
     *  parent_srl = some_id
     *  target_srl = 0
     */
    public function testMoveNodeUnderneathAnother_NewSubcategory()
    {
        $category_repository = new CategoryRepository();
        // At first, the tree is like this:
        // 1. Phones [1]
        //    2. Samsung [2]
        //    3. Nokia [3]
        // We'll move Nokia at the top of the tree
        $category_repository->moveCategory(self::CATEGORY_NOKIA, self::CATEGORY_SAMSUNG, 0);

        // We expect the following tree:
        // 1. Phones [1]
        //   2. Samsung [2]
        //     3. Nokia [3]
        $tree = $category_repository->getCategoriesTree(107);

        $phones = array_shift($tree->children);
        $samsung = array_shift($phones->children);
        $nokia = array_shift($samsung->children);

        $this->assertEquals(self::CATEGORY_PHONES, $phones->category->category_srl);
        $this->assertEquals(1, $phones->category->list_order);

        $this->assertEquals(self::CATEGORY_SAMSUNG, $samsung->category->category_srl);
        $this->assertEquals(2, $samsung->category->list_order);

        $this->assertEquals(self::CATEGORY_NOKIA, $nokia->category->category_srl);
        $this->assertEquals(3, $nokia->category->list_order);

    }

    /**
     * Move any node under another node that has children
     * thus adding it first in the children list
     *
     * Input from client side tree:
     *  parent_srl = some_id
     *  target_srl = 0
     */
    public function testMoveNodeUnderneathAnother_ExistingSubcategory()
    {
        $category_repository = new CategoryRepository();
        // At first, the tree is like this:
        // 1. Phones [1]
        //    2. Samsung [2]
        //    3. Nokia [3]
        // 4. LG [4]
        // We'll move LG underneath Phones (not at the end, that uses target_srl, but at the top, which uses parent_srl)
        $category_repository->moveCategory(self::CATEGORY_LG, self::CATEGORY_PHONES, 0);

        // We expect the following tree:
        // 1. Phones [1]
        //   4. LG [2]
        //   2. Samsung [3]
        //   3. Nokia [4]
        $tree = $category_repository->getCategoriesTree(107);

        $phones = array_shift($tree->children);
        $lg = array_shift($phones->children);
        $samsung = array_shift($phones->children);
        $nokia = array_shift($phones->children);

        $this->assertEquals(self::CATEGORY_PHONES, $phones->category->category_srl);
        $this->assertEquals(1, $phones->category->list_order);

        $this->assertEquals(self::CATEGORY_LG, $lg->category->category_srl);
        $this->assertEquals(2, $lg->category->list_order);

        $this->assertEquals(self::CATEGORY_SAMSUNG, $samsung->category->category_srl);
        $this->assertEquals(3, $samsung->category->list_order);

        $this->assertEquals(self::CATEGORY_NOKIA, $nokia->category->category_srl);
        $this->assertEquals(4, $nokia->category->list_order);
    }


    /**
     * Move any node after another one as the last in the list
     *
     * Input from client side tree:
     *  parent_srl = 0
     *  target_srl = some_id
     */
    public function testMoveNodeAfterAnother_ExistingSubcategoryLast()
    {
        $category_repository = new CategoryRepository();
        // At first, the tree is like this:
        // 1. Phones [1]
        //    2. Samsung [2]
        //    3. Nokia [3]
        // 4. LG [4]
        // We'll move LG underneath Phones (not at the end, that uses target_srl, but at the top, which uses parent_srl)
        $category_repository->moveCategory(self::CATEGORY_LG, 0, self::CATEGORY_NOKIA);

        // We expect the following tree:
        // 1. Phones [1]
        //   2. Samsung [2]
        //   3. Nokia [3]
        //   4. LG [4]
        $tree = $category_repository->getCategoriesTree(107);

        $phones = array_shift($tree->children);
        $samsung = array_shift($phones->children);
        $nokia = array_shift($phones->children);
        $lg = array_shift($phones->children);

        $this->assertEquals(self::CATEGORY_PHONES, $phones->category->category_srl);
        $this->assertEquals(1, $phones->category->list_order);

        $this->assertEquals(self::CATEGORY_SAMSUNG, $samsung->category->category_srl);
        $this->assertEquals(2, $samsung->category->list_order);

        $this->assertEquals(self::CATEGORY_NOKIA, $nokia->category->category_srl);
        $this->assertEquals(3, $nokia->category->list_order);

        $this->assertEquals(self::CATEGORY_LG, $lg->category->category_srl);
        $this->assertEquals(4, $lg->category->list_order);
    }

    /**
     * Move any node after another one in the middle of the list
     *
     * Input from client side tree:
     *  parent_srl = 0
     *  target_srl = some_id
     */
    public function testMoveNodeAfterAnother_ExistingSubcategoryMiddleOfList()
    {
        $category_repository = new CategoryRepository();
        // At first, the tree is like this:
        // 1. Phones [1]
        //    2. Samsung [2]
        //    3. Nokia [3]
        // 4. LG [4]
        // We'll move LG underneath Phones (not at the end, that uses target_srl, but at the top, which uses parent_srl)
        $category_repository->moveCategory(self::CATEGORY_LG, 0, self::CATEGORY_SAMSUNG);

        // We expect the following tree:
        // 1. Phones [1]
        //   2. Samsung [2]
        //   4. LG [3]
        //   3. Nokia [4]
        $tree = $category_repository->getCategoriesTree(107);

        $phones = array_shift($tree->children);
        $samsung = array_shift($phones->children);
        $lg = array_shift($phones->children);
        $nokia = array_shift($phones->children);

        $this->assertEquals(self::CATEGORY_PHONES, $phones->category->category_srl);
        $this->assertEquals(1, $phones->category->list_order);

        $this->assertEquals(self::CATEGORY_SAMSUNG, $samsung->category->category_srl);
        $this->assertEquals(2, $samsung->category->list_order);

        $this->assertEquals(self::CATEGORY_LG, $lg->category->category_srl);
        $this->assertEquals(3, $lg->category->list_order);

        $this->assertEquals(self::CATEGORY_NOKIA, $nokia->category->category_srl);
        $this->assertEquals(4, $nokia->category->list_order);

    }
}

/* End of file CategoryTest.php */
/* Location: ./modules/shop/tests/CategoryTest.php */
