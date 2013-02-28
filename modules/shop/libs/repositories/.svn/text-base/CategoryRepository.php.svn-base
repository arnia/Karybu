<?php
/**
 * File containing the CategoryRepository class
 */
/**
 * Handles database operations for Product Category
 *
 * @author Corina Udrescu (corina.udrescu@arnia.ro)
 */
class CategoryRepository extends BaseRepository
{
	/** @var string Folder where the category images are saved */
	private $category_images_folder = './files/attach/images/shop/%d/product-categories/';

	/**
	 * Returns an absolute url for category with friendly_url $slug
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 *
	 * @param      $slug string
	 *
	 * @param bool $relative
	 * @return string absolute url
	 */
    public static function getUrl($slug, $relative=TRUE)
    {
        return ShopUtils::getUrl("category/$slug", $relative);
    }

	/**
	 * Retrieve a Category object from the database given a friendly url
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 *
	 * @param      $str string
	 *
	 * @param null $module_srl
	 * @throws ShopException
	 * @return Category
	 */
    public function getCategoryByFriendlyUrl($str, $module_srl=NULL)
    {
        if (!is_numeric($module_srl)) {
            $info = Context::get('site_module_info');
            $module_srl = $info->index_module_srl;
        }
        if (!$module_srl) throw new ShopException('Count not get module_srl');
        $output = $this->query('getCategoryByFriendlyUrl', array('friendly_url' => $str, 'module_srl'=>$module_srl));
        return empty($output->data) ? NULL : new Category($output->data);
    }

	/**
	 * Insert a new Product category; returns the ID of the newly created record.
	 *
	 * @param Category $category Category to inserted
	 *
	 * @throws ShopException
	 * @return category_srl int
	 */
	public function insertCategory(Category $category)
	{
		$category->category_srl = getNextSequence();
        $category->list_order = $category->parent_srl;

		$output = executeQuery('shop.insertCategory', $category);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		return $category->category_srl;
	}

	/**
	 * Deletes a product category by $category_srl or $module_srl
	 *
	 * @param stdClass $args Can have the following properties: category_srl or module_srl
	 *
	 * @throws ShopException
	 * @return bool
	 */
	public function deleteCategory($args)
	{
		if(!isset($args->category_srl) && !isset($args->module_srl))
		{
			throw new ShopException("Missing arguments for Product category delete: please provide [category_srl] or [module_srl]");
		}

		// Get category info before deleting it, so we can also delete category image
		if($args->category_srl)
		{
			$category = $this->getCategory($args->category_srl);
		}

        $db = DB::getInstance();
        $db->begin();

		$output = executeQuery('shop.deleteCategory', $args);
		if(!$output->toBool())
		{
            $db->rollback();
			throw new ShopException($output->getMessage(), $output->getError());
		}

		$output = executeQuery('shop.deleteAttributesScope', $args);
		if(!$output->toBool())
		{
            $db->rollback();
			throw new ShopException($output->getMessage(), $output->getError());
		}

		$output = executeQuery('shop.deleteProductCategories', $args);
		if(!$output->toBool())
		{
            $db->rollback();
			throw new ShopException($output->getMessage(), $output->getError());
		}

		if($args->category_srl)
		{
			$this->deleteCategoryImage($category->filename);
		}
		else
		{
			$this->deleteCategoriesImages($args->module_srl);
		}

        $db->commit();

		return TRUE;
	}

	/**
	 * Retrieve a Category object from the database given a srl
	 *
	 * @param int $category_srl by which to select the Category
	 *
	 * @throws ShopException
	 * @return Category
	 */
	public function getCategory($category_srl)
	{
		$args = new stdClass();
		$args->category_srl = $category_srl;

		$output = executeQuery('shop.getCategory', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}

		$category = new Category($output->data);

		return $category;
	}

	/**
	 * Update a product category
	 *
	 * @param Category $category Object to be persisted
	 *
	 * @throws ShopException
	 * @return boolean
	 */
	public function updateCategory(Category $category)
	{
		$output = executeQuery('shop.updateCategory', $category);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		return TRUE;
	}

	/**
	 * Returns the categories as a tree structure
	 *
	 * @param $module_srl
	 * @return CategoryTreeNode
	 */
	public function getNavigationCategoriesTree($module_srl)
	{
		return $this->getCategoriesTree($module_srl, TRUE);
	}

	/**
	 * Get all product categories for a module as a tree
	 * Returns root node
	 *
	 * @param int  $module_srl Module for which to get all categories as a tree
	 *
	 * @param bool $only_included_in_navigation_menu
	 * @return CategoryTreeNode Tree root node
	 */
	public function getCategoriesTree($module_srl, $only_included_in_navigation_menu = FALSE)
	{
		$args = new stdClass();
		$args->module_srl = $module_srl;
		if($only_included_in_navigation_menu)
			$args->include_in_navigation_menu = 'Y';

		// Retrieve categories from database
		try
		{
			$output = $this->query('shop.getCategories', $args, TRUE);
		}
		catch(DbQueryException $e)
		{
			$output = new stdClass();
			$output->data = array();
		}

		// Arrange hierarchically
		$nodes = array();
		$nodes[0] = new CategoryTreeNode();

        $nodes_that_exist = array();
        foreach($output->data as $node)
        {
            $nodes_that_exist[] = $node->category_srl;
        }

		while(count($output->data))
		{
            // Get first element in $output->data
            $first_element_key = array_shift(array_keys($output->data));
            $pc = $output->data[$first_element_key];

            // Only add each node once, otherwise they will overwrite each other
            if(!isset($nodes[$pc->category_srl]))
            {
                $nodes[$pc->category_srl] = new CategoryTreeNode(new Category($pc));
            }

            if(isset($nodes[$pc->parent_srl]))
            {
                $nodes[$pc->parent_srl]->addChild($nodes[$pc->category_srl]);
                unset($output->data[$first_element_key]);
            }
            else
            {
                // Remove category from the begining of the array ..
                unset($output->data[$first_element_key]);
                // .. and add to the end if its parent actually exists
                if(in_array($pc->parent_srl, $nodes_that_exist))
                {
                    $output->data[] = $pc;
                }
            }
		}

		return $nodes[0];
	}

    /**
     * Add categories info to export folder
     * @author Dan Dragan (dev@xpressengine.org)
     *
     * @param array $categories
     *
     * @return boolean
     */
    public function addCategoriesToExportFolder($categories)
    {
        $buff = '';
        //table header for categories csv
        foreach($categories[0]->category as $key => $value)
        {
            if(!in_array($key,array('member_srl','module_srl','regdate','last_update','repo','product_count','cache')))
            {
                if($key == 'category_srl') $buff = $buff.'id,';
                else $buff = $buff.$key.",";
            }
        }
        $buff = $buff."include_in_navigation_menu\r\n";
        //table values  for categories csv
        foreach($categories as $category){
            // add image to temp folder
            $filename = $category->category->filename;
            $export_filename = sprintf('./files/attach/shop/export-import/images/%s',basename($category->category->filename));
            FileHandler::copyFile($filename,$export_filename);

            foreach($category->category as $key => $value){
                if(!in_array($key,array('member_srl','module_srl','regdate','last_update','repo','product_count','filename','cache')))
                {
                    $buff = $buff.$value.",";
                }
                if($key == 'filename'){
                    $buff = $buff.basename($value).",";
                }
            }
            $buff = $buff.$category->category->include_in_navigation_menu."\r\n";
        }
        $category_csv_filename = 'categories.csv';
        $category_csv_path = sprintf('./files/attach/shop/export-import/%s', $category_csv_filename);
        FileHandler::writeFile($category_csv_path, $buff);

        return TRUE;
    }

	/**
	 * import categories from import folder
	 * @author   Dan Dragan (dev@xpressengine.org)
	 *
	 * @param $params
	 * @internal param \for $args module_srl
	 *
	 * @return \ArrayObject|null $category_ids correlation
	 */
    public function insertCategoriesFromImportFolder($params)
    {
        if(file_exists('./files/attach/shop/export-import/categories.csv')){
            $csvString = file_get_contents('./files/attach/shop/export-import/categories.csv');
            $csvData = str_getcsv($csvString, "\n");
            $keys = explode(',',$csvData[0]);

            foreach ($csvData as $idx=>$csvLine){
                if($idx != 0){
                    $cat = explode(',',$csvLine);
                    foreach($cat as $key=>$value){
                        if($keys[$key] != ''){
                            $args[$keys[$key]] = $value;
                        }
                    }
                    $args = (object) $args;
                    $categories[] = $args;
                    unset($args);
                }
            }
            $category_ids = new ArrayObject();
            foreach($categories as $category){
                $cat = new Category($category);
                if($cat->filename) $cat->filename = $this->saveCategoryImage($params->module_srl, $cat->filename,'./files/attach/shop/export-import/images/'.$cat->filename);
                $cat->module_srl = $params->module_srl;
                if($cat->parent_srl){
                    $cat->parent_srl = $category_ids[$cat->parent_srl];
                }
                $cat->category_srl = $this->insertCategory($cat);
                $category_ids[$category->id] = $cat->category_srl;
                $oCategories[] = $cat;
            }
            return $category_ids;
        }  else return NULL;

    }

	/**
	 * Save category image to disc
	 *
	 * @param int    $module_srl        Module's srl
	 * @param string $original_filename Original filename of the uploaded file
	 * @param string $tmp_name          Uploaded file's content
	 *
	 * @return string
	 */
	public function saveCategoryImage($module_srl, $original_filename, $tmp_name)
	{
		$tmp_arr = explode('.', $original_filename);
		$extension = $tmp_arr[count($tmp_arr) - 1];

		$path = sprintf($this->category_images_folder, $module_srl);
		$filename = sprintf('%s%s.%s', $path, uniqid('product-category-'), $extension);
		FileHandler::copyFile($tmp_name, $filename);

		return $filename;
	}

	/**
	 * Delete category image from disc
	 *
	 * @param string $filename Name of the file to delete (category image)
	 *
	 * @return boolean
	 */
	public function deleteCategoryImage($filename)
	{
		if(!$filename)
		{
			return TRUE;
		}

		return FileHandler::removeFile($filename);
	}

	/**
	 * Deletes all category images for a given module
	 *
	 * @param int $module_srl Module
	 *
	 * @return void
	 */
	public function deleteCategoriesImages($module_srl)
	{
		FileHandler::removeFilesInDir(sprintf($this->category_images_folder, $module_srl));
	}

	/**
	 * Returns array of all parent categories
	 *
	 * @param Category $category Current category
	 *
	 * @return Category[]
	 */
	public function getCategoryParents(Category $category)
	{
		$parents = array();

		if($category->parent_srl == 0)
		{
			return $parents;
		}

		$parent_category = $this->getCategory($category->parent_srl);
		$parents[] = $parent_category;
		$rest_of_parents = $this->getCategoryParents($parent_category);

		return array_merge($parents, $rest_of_parents);
	}

    /**
     * Increases the "order" column value of all categories under a parent
	 *
	 * Used for moving a category as first child under a parent node
     */
    public function increaseCategoriesOrder($parent_srl, $order = NULL)
    {
        $args = array( 'parent_srl' => $parent_srl, 'list_order' => $order);
        $this->query('updateCategoriesIncreaseOrder', $args);
    }

    /**
     * Returns the greatest `order` of categories under a parent
     *
     * @param $parent_srl
     */
    public function getMinCategoryOrder($parent_srl)
    {
        $args = array('parent_srl' => $parent_srl);
        $output = $this->query('getMinCategoryOrder', $args);
        return $output->data->max_order;
    }

	/**
	 * Moves a category (change its place in the hierarchy)
	 *
	 * @param $category_srl
	 * @param $parent_category_srl
	 * @param $target_category_srl
	 */
	public function moveCategory($category_srl, $parent_category_srl, $target_category_srl)
    {
        $category = $this->getCategory($category_srl);

        if($parent_category_srl > 0)
        {
            $this->moveNodeUnderneath($category, $parent_category_srl);
            return;
        }

        if($target_category_srl >= 0)
        {
            $this->moveNodeAfter($category, $target_category_srl);
            return;
        }
    }

    /**
     * Move node after a certain node
     *
     * This means under the same parent, after the given category_srl
     */
    public function moveNodeAfter($category, $target_category_srl)
    {
        try
        {
            $target_category = $this->getCategory($target_category_srl);
        }
        catch(Exception $e)
        {
            $target_category = new Category();
        }

        $this->increaseCategoriesOrder($target_category->parent_srl, $target_category->list_order);
        $category->parent_srl = $target_category->parent_srl;
        $category->list_order = $target_category->list_order + 1; // one after the above update and then another one
        $this->updateCategory($category);
        return;
    }

    /**
     * Move node under a certain node
     */
    public function moveNodeUnderneath($category, $parent_category_srl)
    {
        $min_order = $this->getMinCategoryOrder($parent_category_srl);
        $this->increaseCategoriesOrder($parent_category_srl);
        $category->parent_srl = $parent_category_srl;
        $category->list_order = $min_order;
        $this->updateCategory($category);
        return;
    }

    /**
     * Returns a bidimensional array of parent serials corresponding to each category in $category_srls
	 *
     * @param array $category_srls
     * @return array
     * @throws ShopException
     */
    public function getCategoryPaths(array $category_srls)
    {
        if (empty($category_srls)) throw new ShopException('this shouldn\'t be empty');
        $allCategories = $this->get();
        $serials = array();
        foreach ($category_srls as $srl) {
            if ($cat = $this->findBySrl($allCategories, 'category_srl', $srl)) {
                /** @var $cat Category */
                $serials[$srl] = array();
                while ($parent = $this->findBySrl($allCategories, 'category_srl', $cat->parent_srl)) {
                    /** @var $parent Category */
                    $serials[$srl][] = $parent->category_srl;
                    $cat = $parent;
                }
            }
        }
        return $serials;
    }

	/**
	 * Find an object in an array based on the value of one of its properties
	 *
	 * @param array $objects
	 * @param       $fieldName
	 * @param       $value
	 * @return null
	 */
	private function findBySrl(array $objects, $fieldName, $value)
    {
        foreach ($objects as $o) if ($o->$fieldName == $value) return $o; return NULL;
    }

}
/* End of file CategoryRepository.php */
/* Location: ./modules/shop/libs/repositories/CategoryRepository.php */
