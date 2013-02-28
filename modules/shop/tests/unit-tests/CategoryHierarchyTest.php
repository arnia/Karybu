<?php

require_once dirname(__FILE__) . '/../lib/Bootstrap.php';
require_once dirname(__FILE__) . "/../lib/Shop_Generic_Tests.class.php";

require_once dirname(__FILE__) . '/../../libs/model/Category.php';

/**
 *  Test features related to Product categories hierarchical tree display and sorting
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class CategoryHierarchyTest extends Shop_Generic_Tests_DatabaseTestCase
{

    const CATEGORY_SAMSUNG = 451,
        CATEGORY_NOKIA = 473,
        CATEGORY_LG = 474,
        CATEGORY_MASERATI = 475,
        CATEGORY_LAPTOPS = 462,
        CATEGORY_APPLE = 463,
        CATEGORY_FUJITSU = 464,
        CATEGORY_PHONES = 502,
        CATEGORY_CARS = 503,
        CATEGORY_SONY_ERICSSON = 508;

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_categories' => array(
                array('category_srl' => self::CATEGORY_SAMSUNG,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'Samsung','description' => 'sdfsadfsadfsa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120921192939','last_update' => '20120924180718','list_order' => self::CATEGORY_SAMSUNG),
                array('category_srl' => self::CATEGORY_NOKIA,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'Nokia','description' => 'sdfdasf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183752','last_update' => '20120924180728','list_order' => self::CATEGORY_NOKIA),
                array('category_srl' => self::CATEGORY_LG,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'LG','description' => 'sdfsaf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183758','last_update' => '20120924180737','list_order' => self::CATEGORY_LG),
                array('category_srl' => self::CATEGORY_MASERATI,'module_srl' => '107','parent_srl' => self::CATEGORY_CARS,'filename' => NULL,'title' => 'Maserati','description' => 'aaaa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183804','last_update' => '20120924180750','list_order' => self::CATEGORY_MASERATI),
                array('category_srl' => self::CATEGORY_LAPTOPS,'module_srl' => '107','parent_srl' => 0,'filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bad977.','title' => 'Laptops','description' => 'descriere laptops','product_count' => '6','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','list_order' => self::CATEGORY_LAPTOPS),
                array('category_srl' => self::CATEGORY_APPLE,'module_srl' => '107','parent_srl' => self::CATEGORY_LAPTOPS,'filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bafb2a.','title' => 'Apple','description' => '','product_count' => '0','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120924180743','list_order' => self::CATEGORY_APPLE),
                array('category_srl' => self::CATEGORY_FUJITSU,'module_srl' => '107','parent_srl' => self::CATEGORY_APPLE,'filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bb0f06.','title' => 'Fujitsu','description' => '','product_count' => '5','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','list_order' => self::CATEGORY_FUJITSU),
                array('category_srl' => self::CATEGORY_PHONES,'module_srl' => '107','parent_srl' => 0,'filename' => NULL,'title' => 'Phones','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184414','last_update' => '20120924184414','list_order' => self::CATEGORY_PHONES),
                array('category_srl' => self::CATEGORY_CARS,'module_srl' => '107','parent_srl' => 0,'filename' => NULL,'title' => 'Cars','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184441','last_update' => '20120924184441','list_order' => self::CATEGORY_CARS),
                array('category_srl' => self::CATEGORY_SONY_ERICSSON,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'Sony Ericsson','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924185725','last_update' => '20120924185725','list_order' => self::CATEGORY_SONY_ERICSSON),

                array('category_srl' => '4451','module_srl' => '1111','parent_srl' => '4502','filename' => NULL,'title' => 'Samsung','description' => 'sdfsadfsadfsa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120921192939','last_update' => '20120924180718','list_order' => '4451'),
                array('category_srl' => '4473','module_srl' => '1111','parent_srl' => '4451','filename' => NULL,'title' => 'Nokia','description' => 'sdfdasf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183752','last_update' => '20120924180728','list_order' => '4473'),
                array('category_srl' => '4474','module_srl' => '1111','parent_srl' => '4451','filename' => NULL,'title' => 'LG','description' => 'sdfsaf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183758','last_update' => '20120924180737','list_order' => '4475'),
                array('category_srl' => '4475','module_srl' => '1111','parent_srl' => '4503','filename' => NULL,'title' => 'Maserati','description' => 'aaaa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183804','last_update' => '20120924180750','list_order' => '4475'),
                array('category_srl' => '4462','module_srl' => '1111','parent_srl' => '0','filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bad977.','title' => 'Laptops','description' => 'descriere laptops','product_count' => '6','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','list_order' => '4462'),
                array('category_srl' => '4463','module_srl' => '1111','parent_srl' => '4462','filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bafb2a.','title' => 'Apple','description' => '','product_count' => '0','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120924180743','list_order' => '4463'),
                array('category_srl' => '4464','module_srl' => '1111','parent_srl' => '4463','filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bb0f06.','title' => 'Fujitsu','description' => '','product_count' => '5','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','list_order' => '4464'),
                array('category_srl' => '4502','module_srl' => '1111','parent_srl' => '0','filename' => NULL,'title' => 'Phones','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184414','last_update' => '20120924184414','list_order' => '4502'),
                array('category_srl' => '4503','module_srl' => '1111','parent_srl' => '0','filename' => NULL,'title' => 'Cars','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184441','last_update' => '20120924184441','list_order' => '4503'),
                array('category_srl' => '4508','module_srl' => '1111','parent_srl' => '4502','filename' => NULL,'title' => 'Sony Ericsson','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924185725','last_update' => '20120924185725','list_order' => '4508'),

                array('category_srl' => '5451','module_srl' => '2222','parent_srl' => '5502','filename' => NULL,'title' => 'Samsung','description' => 'sdfsadfsadfsa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120921192939','last_update' => '20120924180718','list_order' => '5451'),
                array('category_srl' => '5473','module_srl' => '2222','parent_srl' => '5451','filename' => NULL,'title' => 'Nokia','description' => 'sdfdasf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183752','last_update' => '20120924180728','list_order' => '5473'),
                array('category_srl' => '5474','module_srl' => '2222','parent_srl' => '5451','filename' => NULL,'title' => 'LG','description' => 'sdfsaf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183758','last_update' => '20120924180737','list_order' => '5474'),
                array('category_srl' => '5475','module_srl' => '2222','parent_srl' => '5474','filename' => NULL,'title' => 'Maserati','description' => 'aaaa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183804','last_update' => '20120924180750','list_order' => '1'),
                array('category_srl' => '5462','module_srl' => '2222','parent_srl' => '0','filename' => './files/attach/images/shop/2222/product-categories/product-category-505dc98bad977.','title' => 'Laptops','description' => 'descriere laptops','product_count' => '6','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','list_order' => '5462'),
                array('category_srl' => '5463','module_srl' => '2222','parent_srl' => '5462','filename' => './files/attach/images/shop/2222/product-categories/product-category-505dc98bafb2a.','title' => 'Apple','description' => '','product_count' => '0','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120924180743','list_order' => '5463'),
                array('category_srl' => '5464','module_srl' => '2222','parent_srl' => '5463','filename' => './files/attach/images/shop/2222/product-categories/product-category-505dc98bb0f06.','title' => 'Fujitsu','description' => '','product_count' => '5','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','list_order' => '5464'),
                array('category_srl' => '5502','module_srl' => '2222','parent_srl' => '0','filename' => NULL,'title' => 'Phones','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184414','last_update' => '20120924184414','list_order' => '5502'),
                array('category_srl' => '5503','module_srl' => '2222','parent_srl' => '0','filename' => NULL,'title' => 'Cars','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184441','last_update' => '20120924184441','list_order' => '5503'),
                array('category_srl' => '5508','module_srl' => '2222','parent_srl' => '5451','filename' => NULL,'title' => 'Sony Ericsson','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924185725','last_update' => '20120924185725','list_order' => '511')

            ),
            'xe_shop_products' => array(),
            'xe_shop_product_categories' => array()
        ));
    }

    /**
     * Test a more mingled hierarchy, with more levels
     */
    public function testMoreComplicatedTreeHierarchy()
    {
        // Retrieve tree
        $shopModel = &getModel('shop');
        $repository = $shopModel->getCategoryRepository();

        $tree = $repository->getCategoriesTree(107);

        // Check hierarchy
        $this->assertNotNull($tree);
        $this->assertNull($tree->category); // Root node should not have any product category associated

        foreach($tree->children as $id => $node)
        {
            if($id == self::CATEGORY_LAPTOPS)
            {
                $this->assertEquals(self::CATEGORY_LAPTOPS, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));

                $first_child_node = array_shift(array_values($node->children));
                $this->assertEquals(self::CATEGORY_APPLE, $first_child_node->category->category_srl);
                $this->assertEquals(1, count($first_child_node->children));

                $first_child_of_child_node = array_shift(array_values($first_child_node->children));
                $this->assertEquals(self::CATEGORY_FUJITSU, $first_child_of_child_node->category->category_srl);
                $this->assertEquals(0, count($first_child_of_child_node->children));
            }
            elseif($id == self::CATEGORY_PHONES)
            {
                $this->assertEquals(self::CATEGORY_PHONES, $node->category->category_srl);
                $this->assertEquals(4, count($node->children));
            }
            elseif($id == self::CATEGORY_CARS)
            {
                $this->assertEquals(self::CATEGORY_CARS, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));
            }
            else
            {
                $this->fail("Unexpected node found as root: " . $id);
            }
        }
    }

    /**
     * Test a more mingled hierarchy, with more levels
     *
     * This bug caused two nodes to be ignored, because the sorting was done by parent srl
     * but not taking into account depth, thus the missing nodes
     */
    public function testMoreComplicatedTreeHierarchyAfterManuallyChanginParentsAndOrder()
    {
        // Retrieve tree
        $shopModel = &getModel('shop');
        $repository = $shopModel->getCategoryRepository();

        $tree = $repository->getCategoriesTree(1111);

        // Check hierarchy
        $this->assertNotNull($tree);
        $this->assertNull($tree->category); // Root node should not have any product category associated

        foreach($tree->children as $id => $node)
        {
            if($id == '4' . self::CATEGORY_LAPTOPS)
            {
                $this->assertEquals('4' . self::CATEGORY_LAPTOPS, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));

                $first_child_node = array_shift(array_values($node->children));
                $this->assertEquals('4' . self::CATEGORY_APPLE, $first_child_node->category->category_srl);
                $this->assertEquals(1, count($first_child_node->children));

                $first_child_of_child_node = array_shift(array_values($first_child_node->children));
                $this->assertEquals('4' . self::CATEGORY_FUJITSU, $first_child_of_child_node->category->category_srl);
                $this->assertEquals(0, count($first_child_of_child_node->children));
            }
            elseif($id == '4' . self::CATEGORY_PHONES)
            {
                $this->assertEquals('4' . self::CATEGORY_PHONES, $node->category->category_srl);
                $this->assertEquals(2, count($node->children));

                $samsung = array_shift($node->children);
                $this->assertEquals('4' . self::CATEGORY_SAMSUNG, $samsung->category->category_srl);
                $this->assertEquals(2, count($samsung->children));

                $nokia = array_shift($samsung->children);
                $this->assertEquals('4' . self::CATEGORY_NOKIA, $nokia->category->category_srl);

                $lg = array_shift($samsung->children);
                $this->assertEquals('4' . self::CATEGORY_LG, $lg->category->category_srl);

                $sony = array_shift($node->children);
                $this->assertEquals('4' . self::CATEGORY_SONY_ERICSSON, $sony->category->category_srl);
            }
            elseif($id == '4' . self::CATEGORY_CARS)
            {
                $this->assertEquals('4' . self::CATEGORY_CARS, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));
            }
            else
            {
                $this->fail("Unexpected node found as root: " . $id);
            }
        }
    }


    /**
     * Test a more mingled hierarchy, with more levels
     *
     * This bug caused two nodes to be ignored, because the sorting was done by parent srl
     * but not taking into account depth, thus the missing nodes
     */
    public function testMoreComplicatedTreeHierarchyAfterManuallyChanginParentsAndOrder2()
    {
        // Retrieve tree
        $shopModel = &getModel('shop');
        $repository = $shopModel->getCategoryRepository();

        $tree = $repository->getCategoriesTree(2222);

        // Check hierarchy
        $this->assertNotNull($tree);
        $this->assertNull($tree->category); // Root node should not have any product category associated

        foreach($tree->children as $id => $node)
        {
            if($id == '5' . self::CATEGORY_LAPTOPS)
            {
                $this->assertEquals('5' . self::CATEGORY_LAPTOPS, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));

                $first_child_node = array_shift(array_values($node->children));
                $this->assertEquals('5' . self::CATEGORY_APPLE, $first_child_node->category->category_srl);
                $this->assertEquals(1, count($first_child_node->children));

                $first_child_of_child_node = array_shift(array_values($first_child_node->children));
                $this->assertEquals('5' . self::CATEGORY_FUJITSU, $first_child_of_child_node->category->category_srl);
                $this->assertEquals(0, count($first_child_of_child_node->children));
            }
            elseif($id == '5' . self::CATEGORY_PHONES)
            {
                $this->assertEquals('5' . self::CATEGORY_PHONES, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));

                    $samsung = array_shift($node->children);
                    $this->assertEquals('5' . self::CATEGORY_SAMSUNG, $samsung->category->category_srl);
                    $this->assertEquals(3, count($samsung->children));


                        $sony = array_shift($samsung->children);
                        $this->assertEquals('5' . self::CATEGORY_SONY_ERICSSON, $sony->category->category_srl);

                        $nokia = array_shift($samsung->children);
                        $this->assertEquals('5' . self::CATEGORY_NOKIA, $nokia->category->category_srl);

                        $lg = array_shift($samsung->children);
                        $this->assertEquals('5' . self::CATEGORY_LG, $lg->category->category_srl);
                        $this->assertEquals(1, count($lg->children), "LG does not have any children but it should contain Maserati");

                            $maserati = array_shift($lg->children);
                            $this->assertEquals('5' . self::CATEGORY_MASERATI, $maserati->category->category_srl);
                            $this->assertEquals(0, count($maserati->children));
            }
            elseif($id == '5' . self::CATEGORY_CARS)
            {
                $this->assertEquals('5' . self::CATEGORY_CARS, $node->category->category_srl);
                $this->assertEquals(0, count($node->children));
            }
            else
            {
                $this->fail("Unexpected node found as root: " . $id);
            }
        }
    }
}

/* End of file CategoryTest.php */
/* Location: ./modules/shop/tests/CategoryTest.php */
