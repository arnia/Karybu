<?php
/**
 * Handles database operations for Product
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class ProductRepository extends BaseRepository
{

    /**
     * Returns an absolute url for product with friendly_url $slug
     * @param $slug
     * @param bool $relative
     * @return string
     */
    public static function getUrl($slug, $relative=true)
    {
        return ShopUtils::getUrl("product/$slug", $relative);
    }

    /**
     * Insert a new Product  returns the ID of the newly created record
     * @param Product $product
     * @return mixed
     * @throws ShopException
     */
    public function insertProduct(Product $product)
	{
		$product->product_srl = getNextSequence();

        if($product->discount_price >= $product->price){
            throw new ShopException("Discount price is bigger than normal price");
        }

		$output = executeQuery('shop.insertProduct', $product);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		else
		{
			$this->insertProductCategories($product);
			$this->insertProductAttributes($product);
			if($product->product_type == 'configurable') $this->insertProductConfigurableAttributes($product);
			$this->insertProductImages($product);
		}

        $slug = ShopUtils::slugify($product->title);

		return $product->product_srl;
	}

    /**
     * Insert product attributes
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function insertProductAttributes(Product $product)
	{
		$valid_attributes = $this->getProductCategoriesAttributes($product);

		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		foreach($product->attributes as $attribute_srl => $attribute_value)
		{
			if(!isset($valid_attributes)){
               continue;
            } else {
                if(!in_array($attribute_srl, $valid_attributes)) continue;
            }
			$args->attribute_srl = $attribute_srl;

			if(is_array($attribute_value))
			{
				foreach($attribute_value as $array_value)
				{
					$args->attribute_value = $array_value;
					$output = executeQuery('shop.insertProductAttribute', $args);
					if(!$output->toBool())
					{
						throw new ShopException($output->getMessage(), $output->getError());
					}
				}
			}
			else
			{
				$args->attribute_value = $attribute_value;
				$output = executeQuery('shop.insertProductAttribute', $args);
				if(!$output->toBool())
				{
					throw new ShopException($output->getMessage(), $output->getError());
				}
			}
		}
		return TRUE;
	}

	/**
	 * Insert product images
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function insertProductImages(Product $product)
	{
		$shopModel = getModel('shop');
		$imageRepository = $shopModel->getImageRepository();
		if (is_array($product->images)) {
            foreach($product->images as $image){
                $image->product_srl = $product->product_srl;
                $image->module_srl = $product->module_srl;
                $image->member_srl = $product->member_srl;
                $imageRepository->insertImage($image);
            }
        }
		return TRUE;
	}

    /**
     * Insert product configurable attributes
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function insertProductConfigurableAttributes(Product $product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		foreach($product->configurable_attributes as $config_attribute_srl => $config_attribute_title){
			$args->attribute_srl = $config_attribute_srl;
			$output = executeQuery('shop.insertProductAttribute',$args);
			if(!$output->toBool()) throw new ShopException($output->getMessage(), $output->getError());
		}
		return TRUE;
	}

    /**
     * Given a product, returns all attributes the
     * product can have according to the categories
     * it belongs to
     * @param Product $product
     * @return array
     * @throws ShopException
     */
    public function getProductCategoriesAttributes(Product $product)
	{
		$args = new stdClass();
		$args->category_srls = $product->categories;

		if(isset($args->category_srls)) $output = executeQueryArray('shop.getCategoryAttributes', $args);
            else return;
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}

		$attributes_list = array();
		foreach($output->data as $attribute)
		{
			$attributes_list[] = $attribute->attribute_srl;
		}

		// If this is an associated product, also add parent's configurable attributes as valid
		if($product->parent_product_srl)
		{
			/**
			 * @var ConfigurableProduct $parent_product
			 */
			$parent_product = $this->getProduct($product->parent_product_srl);
			$attributes_list = array_merge($attributes_list, array_keys($parent_product->configurable_attributes));
		}

		return $attributes_list;

	}

    /**
     * Insert product categories
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function insertProductCategories(Product $product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
        $args->module_srl = $product->module_srl;
        if(isset($product->categories)){
            foreach($product->categories as $category)
            {
                $args->category_srl = $category;

                // Insert product category
                $output = executeQuery('shop.insertProductCategories', $args);
                if(!$output->toBool())
                {
                    throw new ShopException($output->getMessage(), $output->getError());
                }
                $this->updateProductCategoryCount($args);
            }
        }
		return TRUE;
	}

    /**
     * Updates product category count
     * @param $args
     * @throws ShopException
     */
    public function updateProductCategoryCount($args){
        $shopInfo = new ShopInfo($args->module_srl);
        // Get number of products in category
        $args->status = "enabled";
        if($shopInfo->getOutOfStockProducts() == 'N') $args->in_stock = "Y";
        $count_output = executeQuery('shop.getProductsInCategoryCount', $args);
        if(!$count_output->toBool())
        {
            throw new ShopException($count_output->getMessage(), $count_output->getError());
        }



        // Update product count
        $update_args = new stdClass();
        $update_args->category_srl = $args->category_srl;
        $update_args->product_count = $count_output->data->product_count;
        $output = executeQuery('shop.updateCategory', $update_args);
        if(!$output->toBool())
        {
            throw new ShopException($output->getMessage(), $output->getError());
        }
    }

    /**
     * Deletes a product by $product_srl or $module_srl
     * @param $args
     * @return bool
     * @throws ShopException
     */
    public function deleteProduct($args)
	{
        if (is_array($args)) $args = (object) $args;
		if(!isset($args->product_srl)) {
            throw new ShopException("Missing arguments for Product delete: please provide [product_srl] or [module_srl]");
        }
        $product = $this->getProduct($args->product_srl);
		$this->query('deleteProduct',$args);



        if ($product->product_type == 'configurable') $this->deleteAssociatedProducts($product);
		$this->deleteProductCategories($product);
		$this->deleteProductAttributes($product);
		$this->deleteProductImages($product);

		return TRUE;
	}

    /**
     * Deletes more products by $product_srls
     * @param $args
     * @return bool
     * @throws ShopException
     */
    public function deleteProducts($args)
    {
        if(!isset($args->product_srls)) {
            throw new ShopException("Missing arguments for Products delete: please provide [product_srls]");
        }
        foreach($args->product_srls as $product_srl){
            $products[] = $this->getProduct($product_srl);
        }
		$this->query('deleteProducts',$args);
		$args->parent_product_srls = $args->product_srls;
		$this->query('deleteAssociatedProducts',$args);
		$this->query('deleteProductCategories',$args);
        foreach($products as $product){
            if(isset($product->categories)){
                foreach($product->categories as $category){
                    $args->category_srl = $category;
                    $this->updateProductCategoryCount($args);
                }
            }

        }
		$this->query('deleteProductAttributes',$args);
		$this->query('deleteProductImages',$args);

		foreach($args->product_srls as $product_srl){
			$path = sprintf('./files/attach/images/shop/%d/product-images/%d/', $args->module_srl,$product_srl);
			FileHandler::removeDir($path);
		}

        return TRUE;
    }

    /**
     * Delete product categories
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function deleteProductCategories(Product &$product)
    {
		$old_product = new SimpleProduct();
		$old_product->product_srl = $product->product_srl;
		$old_product->module_srl = $product->module_srl;
		$this->getProductCategories($old_product);

		$args = new stdClass();
        $args->product_srls[] = $product->product_srl;
        $output = executeQuery('shop.deleteProductCategories',$args);
        if (!$output->toBool()) throw new ShopException($output->getMessage(), $output->getError());
        $args->module_srl = $product->module_srl;
        if(isset($product->categories)){
            foreach($product->categories as $category){
                $args->category_srl = $category;
                $this->updateProductCategoryCount($args);
            }
        }
		if(isset($old_product->categories)){
			foreach($old_product->categories as $category){
				$args->category_srl = $category;
				$this->updateProductCategoryCount($args);
			}
		}
        return TRUE;
    }

	/**
	 * Delete associated products
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function deleteAssociatedProducts(Product $product)
	{
		return $this->query('deleteAssociatedProducts', array('parent_product_srls' => $product->product_srl));
	}

    /**
     * Delete product attributes
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function deleteProductAttributes(Product &$product)
	{
		if(!$product->product_srl) {
			throw new ShopException("Invalid arguments! Please provide product_srl for delete atrributes.");
		}

		$args = new stdClass();
		$args->product_srls = array($product->product_srl);
		$output = executeQuery('shop.deleteProductAttributes', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		return TRUE;
	}

    /**
     * Delete product images
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function deleteProductImages(Product &$product)
	{
		if(!$product->product_srl)
		{
			throw new ShopException("Invalid arguments! Please provide product_srl for delete attributes.");
		}

		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$output = executeQuery('shop.deleteProductImages', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		$path = sprintf('./files/attach/images/shop/%d/product-images/%d/', $product->module_srl,$product->product_srl);
		FileHandler::removeDir($path);
		return TRUE;
	}

    /**
     * Retrieve a Product object from the database given a srl
     * @param $product_srl
     * @param bool $loadImages
     * @return ConfigurableProduct|null|SimpleProduct
     * @throws ShopException
     */
    public function getProduct($product_srl, $loadImages = true)
	{
		$args = new stdClass();
		$args->product_srl = $product_srl;
		$output = $this->query('getProduct', $args);
		// If product does not exist, return null
		if(!$output->data) return NULL;
		if($output->data->product_type == 'simple') {
			$product = new SimpleProduct($output->data);
		}
		else {
			$product = new ConfigurableProduct($output->data);
			// Get associated products
			$associated_products_args = new stdClass();
			$associated_products_args->configurable_product_srls = array($product->product_srl);
			$associated_products_output = executeQueryArray('shop.getAssociatedProducts', $associated_products_args);
			if(!$associated_products_output->toBool()) {
				throw new ShopException($associated_products_output->getMessage());
			}
			$associated_products = $associated_products_output->data;
			foreach($associated_products as $associated_product)
			{
				$product_object = new SimpleProduct($associated_product);
				$this->getProductAttributes($product_object);
				$product->associated_products[] = $product_object;
			}
		}
        $this->getProductCategories($product);
		$this->getProductAttributes($product);
		if($loadImages) $this->getProductImages($product);
		return $product;
	}

    /**
     * Retrieve a Product object from the database given a friendly url string
     * @param $str
     * @param null $module_srl
     * @return null|SimpleProduct
     * @throws ShopException
     */
    public function getProductByFriendlyUrl($str, $module_srl=null)
    {
        if (!is_numeric($module_srl)) { //get current module_srl
            $info = Context::get('site_module_info');
            $module_srl = $info->index_module_srl;
        }
        if (!$module_srl) throw new ShopException('Count not get module_srl');
        $output = $this->query('getProductByFriendlyUrl', array('friendly_url' => $str, 'module_srl' => $module_srl));
        return empty($output->data) ? NULL : new SimpleProduct($output->data);
    }

    /**
     * Retrieve product categories
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function getProductCategories(Product &$product)
    {
		$args = new stdClass();
        $args->product_srl = $product->product_srl;
        $output = executeQuery('shop.getProductCategories',$args);
        if (!$output->toBool()) throw new ShopException($output->getMessage(), $output->getError());
        if(!is_array($output->data)){
            $product->categories[] = $output->data->category_srl;
        }else{
            foreach($output->data as $item){
                $product->categories[] = $item->category_srl;
            }
        }
        return TRUE;
    }

    /**
     * Retrieve product attributes
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function getProductAttributes(Product &$product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$output = executeQueryArray('shop.getProductAttributes', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}

		foreach($output->data as $attribute)
		{
			if($attribute->value)
			{
				if(!isset($product->attributes[$attribute->attribute_srl]))
				{
					$product->attributes[$attribute->attribute_srl] = $attribute->value;
				}
				else // We have an array of values (like in multiple selects)
				{
					if(is_array($product->attributes[$attribute->attribute_srl]))
					{
						$values = $product->attributes[$attribute->attribute_srl];
					}
					else
					{
						$values = array();
						$values[] = $product->attributes[$attribute->attribute_srl];
					}
					$values[] = $attribute->value;
					$product->attributes[$attribute->attribute_srl] = $values;
				}
			}
			else $product->configurable_attributes[$attribute->attribute_srl] = $attribute->title;
		}

		return TRUE;
	}

    /**
     * Retrieve product images
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function getProductImages(Product &$product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$shopModel = getModel('shop');
		$imageRepository = $shopModel->getImageRepository();
		$output = executeQueryArray('shop.getProductImages', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}

		foreach($output->data as $image)
		{
			$oImage = new ProductImage($image);
			$product->images[$image->filename] = $oImage;
		}

		return TRUE;
	}

    /**
     * Create product from parent product
     * @param ConfigurableProduct $parent_product
     * @param array $values
     * @return SimpleProduct
     */
    public function createProductFromParent(ConfigurableProduct $parent_product, array $values)
	{
		$product = new SimpleProduct();
		$product->member_srl = $parent_product->member_srl;
		$product->module_srl = $parent_product->module_srl;
		$product->parent_product_srl = $parent_product->product_srl;
		$product->product_type = 'simple';
		$product->title = $parent_product->title.'_'.implode('_',$values);
		$product->sku = $parent_product->sku.'_'.implode('_',$values);
		$product->price = $parent_product->price;
		$product->categories = $parent_product->categories;
		$configurable_attributes_srls = array_keys($parent_product->configurable_attributes);
		for($i=0;$i<count($values);$i++){
			$product->attributes[$configurable_attributes_srls[$i]] = $values[$i];
		}

		return $product;
	}

    /**
     * Retrieve a Product List object from the database given a module_srl
     * @param $args
     * @param bool $loadAttributes
     * @param bool $loadImages
     * @param null $orderBy
     * @return object
     * @throws ShopException
     */
    public function getProductList($args, $loadAttributes = FALSE, $loadImages = FALSE, $orderBy=null)
    {
        if (!isset($args->module_srl)) throw new ShopException("Missing arguments for get product list : please provide [module_srl]");
		if (!$args->page) $args->page = 1;
        //$query = ($args->category_srls && !empty($args->category_srls) ? 'getProductListByCategory' : 'getProductList');
        $query = ($args->category_srls && !empty($args->category_srls) ? 'getProductList2WithCategories' : 'getProductList2');
        $output = $this->query($query, $args, true);
		// Get top level products
		$confProdSrls = $products = array();
        foreach ($output->data as $row) {
			if ($row->product_type == 'simple') {
				$product = new SimpleProduct($row);
				if ($loadAttributes) $this->getProductAttributes($product);
                if($loadImages) $this->getProductImages($product);
			}
			else {
				$product = new ConfigurableProduct($row);
				if ($loadAttributes) $this->getProductAttributes($product);
                if($loadImages) $this->getProductImages($product);
				$confProdSrls[] = $row->product_srl;
			}
            $products[$row->product_srl] = $product;
        }
		if (count($confProdSrls)) {
			// Get associated products and link to their parents
            $associatedProds = $this->query('getAssociatedProducts', array(
                    'module_srl' => $args->module_srl,
                    'configurable_product_srls' => $confProdSrls
                ), true)->data;
			foreach ($associatedProds as $associatedProd) {
				$product = new SimpleProduct($associatedProd);
				if ($loadAttributes) $this->getProductAttributes($product);
                if ($loadImages) $this->getProductImages($product);
				$products[$associatedProd->parent_product_srl]->associated_products[] = $product;
			}
		}
        $output->products = $products;
        return $output;
    }

    /**
     * Retrieve only featured products
     *
     * // TODO Stop duplicating code - should call getProductsList instead
     * @param $args
     * @param bool $loadAttributes
     * @param bool $loadImages
     * @return object
     * @throws ShopException
     */
    public function getFeaturedProducts($args, $loadAttributes = FALSE, $loadImages = FALSE){
        if (!isset($args->module_srl)) throw new ShopException("Missing arguments for get product list : please provide [module_srl]");
        $args->is_featured = 'Y';
        $output = $this->query('getFeaturedProducts', $args, true);
        // Get top level products
        $configurable_products = array();
        $products = array();
        foreach ($output->data as $product) {
            if ($product->product_type == 'simple') {
                $product_object = new SimpleProduct($product);
                if($loadAttributes) $this->getProductAttributes($product_object);
                if($loadImages) $this->getProductImages($product_object);
            }
            else {
                $product_object = new ConfigurableProduct($product);
                if($loadAttributes) $this->getProductAttributes($product_object);
                if($loadImages) $this->getProductImages($product_object);
                $configurable_products[] = $product->product_srl;
            }
            $products[$product->product_srl] = $product_object;
        }
        if (!empty($configurable_products)) {
            // Get associated products and link to their parents
            $associated_products_args = new stdClass();
            $associated_products_args->module_srl = $args->module_srl;
            $associated_products_args->configurable_product_srls = $configurable_products;
            $associated_products_output = $this->query('getAssociatedProducts', $associated_products_args, true);
            $associated_products = $associated_products_output->data;
            foreach ($associated_products as $associated_product) {
                $product_object = new SimpleProduct($associated_product);
                if($loadAttributes) $this->getProductAttributes($product_object);
                if($loadImages) $this->getProductImages($product_object);
                $products[$associated_product->parent_product_srl]->associated_products[] = $product_object;
            }
        }
        $output->products = $products;
        return $output;
    }

    /**
     * Retrieve a all products and all product information by module_srl
     * @param $args
     * @return array
     * @throws ShopException
     */
    public function getAllProducts($args){
		if(!isset($args->module_srl))
			throw new ShopException("Missing arguments for get product list : please provide [module_srl]");

		$output = $this->query('getProductSrls',$args,true);

		foreach($output->data as $product){
			$product = $this->getProduct($product->product_srl);
			$products[] = $product;
		}
		return $products;
	}

	/**
	 * Add products info to export folder
	 * @author Dan Dragan (dev@xpressengine.org)
	 *
	 * @param array $products
	 *
	 * @return boolean
	 */
	public function addProductsToExportFolder($products)
	{

        $buff = '';
		//table header for products csv
		foreach($products[0] as $key => $value)
		{
			if(!in_array($key,array('member_srl','module_srl','regdate','last_update','primary_image','repo','associated_products','cache')))
			{
                if($key == 'product_srl') $buff = $buff.'id,';
				else $buff = $buff.$key.",";
			}
		}
		$buff = $buff."configurable_attributes\r\n";
		//table values  for products  csv
		foreach($products as $product){
            // add images to temp folder
            foreach($product->images as $image){
                $path = sprintf('./files/attach/images/shop/%d/product-images/%d/', $image->module_srl , $image->product_srl);
                $filename = sprintf('%s%s', $path, $image->filename);
                $export_filename = sprintf('./files/attach/shop/export-import/images/%s',$image->product_srl.$image->filename);
                FileHandler::copyFile($filename,$export_filename);
            }
			foreach($product as $key => $value){
				if(!in_array($key,array('member_srl','module_srl','regdate','last_update','primary_image','primary_image_filename','repo','categories','attributes','images','associated_products','configurable_attributes','cache')))
				{
					$buff = $buff.str_replace(",",";;",$value).",";
				}
                if($key == 'primary_image_filename') {
                    if(isset($product->primary_image_filename)) $buff = $buff.$product->product_srl.$value.",";
                    else $buff = $buff.",";
                }
                $product_categories = '';
                if($key == 'categories'){
                    foreach($value as $category){
                        if($product_categories == '') $product_categories = $category;
                        else $product_categories = $product_categories.'|'.$category;
                    }
                    $buff = $buff.$product_categories.",";
                }

                $product_attributes = '';
                if($key == 'attributes'){
                    foreach($value as $attribute_srl => $attribute_value){
                        if($product_attributes == '') $product_attributes = $attribute_srl.'='.$attribute_value;
                        else $product_attributes = $product_attributes.'|'.$attribute_srl.'='.$attribute_value;
                    }
                    $buff = $buff.$product_attributes.",";
                }

                $images = '';
                if($key == 'images'){
                    foreach($value as $image){
                        if($image->filename){
                            if($images == '') $images = $product->product_srl.$image->filename;
                            else $images = $images.'|'.$product->product_srl.$image->filename;
                        }
                    }
                    $buff = $buff.$images.",";
                }


                if($key == 'configurable_attributes'){
                    $configurable_attributes = '';
                    foreach($value as $attribute_srl => $attribute_value){
                        if($configurable_attributes == '') $configurable_attributes = $attribute_srl;
                        else $configurable_attributes = $configurable_attributes.'+'.$attribute_srl;
                    }
                }

            }
			$buff = $buff.$configurable_attributes."\r\n";
		}
        $product_csv_filename = 'products.csv';
        $product_csv_path = sprintf('./files/attach/shop/export-import/%s', $product_csv_filename);
        FileHandler::writeFile($product_csv_path, $buff);

        return TRUE;
	}

    /**
     * import products from import folder
     * @param $params
     */
    public function insertProductsFromImportFolder($params)
    {
        $shopModel = getModel('shop');
        $imageRepository = $shopModel->getImageRepository();

        $csvString = file_get_contents('./files/attach/shop/export-import/products.csv');
        $csvData = str_getcsv($csvString, "\n");
        $keys = explode(',',$csvData[0]);

        foreach ($csvData as $idx=>$csvLine){
            if($idx != 0){
                $cat = explode(',',$csvLine);
                foreach($cat as $key=>$value){
                    if($keys[$key] != ''){
                        $args[$keys[$key]] = str_replace(";;",",",$value);
                    }
                }
                $args = (object) $args;
                $products[] = $args;
                unset($args);
            }
        }
        $product_ids = new ArrayObject();
        foreach($products as $product){
            $product->module_srl = $params->module_srl;
            $product->member_srl = $params->member_srl;

            //correlate new category srls
            $product->categories = explode('|',$product->categories);
            unset($new_categories);
            foreach($product->categories as $category){
                if(isset($params->category_ids[$category])) $new_categories[] = $params->category_ids[$category];
            }
            unset($product->categories);
            $product->categories = $new_categories;

            if($product->qty == "") unset($product->qty);
            if($product->discount_price == "") unset($product->discount_price);
            if($product->weight == "") unset($product->weight);
            if($product->parent_product_srl == "") unset($product->parent_product_srl);

            //correlate product attributes
            $atts = explode('|',$product->attributes);
            unset($product->attributes);
            foreach($atts as $att){
               $aux = explode('=',$att);
               $aux[0] = $params->attribute_ids[$aux[0]];
               $product->attributes[$aux[0]] = $aux[1];
            }

            //correleate product images
            $images = explode('|',$product->images);
            unset($product->images);
            $args = new stdClass();
            if(isset($images)){
                foreach($images as $image){
                    $args->source_filename = sprintf('./files/attach/shop/export-import/images/%s',$image);
                    $args->file_size = filesize($args->source_filename);
                    if($image == $product->primary_image_filename) $args->is_primary = 'Y';
                    else $args->is_primary = 'N';
                    $args->filename = $image;
                    $new_image = new ProductImage($args);
                    $product->images[] = $new_image;
                }
            }


            if($product->product_type == 'simple') {
                $prod = new SimpleProduct($product);
            }
            elseif($product->product_type == 'configurable') {
                $product->configurable_attributes = explode('+',$product->configurable_attributes);
                //correlate configurable attributes
                unset($new_configurable_attributes);
                foreach($product->configurable_attributes as $configurable_attribute){
                    if(isset($params->attribute_ids[$configurable_attribute])) $new_configurable_attributes[] = $params->attribute_ids[$configurable_attribute];
                }
                unset($product->configurable_attributes);
                $product->configurable_attributes = $new_configurable_attributes;
                $prod = new ConfigurableProduct($product);
            }
            if($prod->parent_product_srl){
                $prod->parent_product_srl= $product_ids[$prod->parent_product_srl];
            }
            $prod->product_srl = $this->insertProduct($prod);
            $product_ids[$product->id] = $prod->product_srl;
            $this->updatePrimaryImageFilename($prod);
            $oProducts[] = $prod;
        }
    }

    /**
     * Update a product
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function updateProduct(Product $product)
	{
        if($product->discount_price >= $product->price){
            throw new ShopException("Discount price is bigger than normal price");
        }
        $output = executeQuery('shop.updateProduct', $product);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		} else {
            $this->updateProductCategories($product);
			$this->updateProductAttributes($product);
            if($product->product_type == 'configurable') $this->insertProductConfigurableAttributes($product);
			$this->updateProductImages($product);
        }
		return TRUE;
	}

    /**
     * Update product categories
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $product Product
     * @return boolean
     */
    public function updateProductCategories(Product &$product)
    {
        $this->deleteProductCategories($product);
        $this->insertProductCategories($product);
        return TRUE;
    }

	/**
	 * Update product attributes
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function updateProductAttributes(Product &$product)
	{
		$this->deleteProductAttributes($product);
		$this->insertProductAttributes($product);
		return TRUE;
	}

    /**
     * Update product images
     * @param Product $product
     * @return bool
     * @throws ShopException
     */
    public function updateProductImages(Product &$product)
	{
		$args = new stdClass();
		$args->image_srls = $product->delete_images;
		$shopModel = getModel('shop');
		$imageRepository = $shopModel->getImageRepository();
		$this->updatePrimaryImage($product);
		if(isset($args->image_srls)){
			$delete_images = $imageRepository->getImages($args->image_srls);
			if(in_array($product->primary_image, $args->image_srls)) {
				$this->setNewPrimaryImage($product);
			}
			foreach($delete_images as $delete_image){
				$path = sprintf('./files/attach/images/shop/%d/product-images/%d/%s', $product->module_srl,$product->product_srl,$delete_image->filename);
				FileHandler::removeFile($path);
			}
			$output = executeQuery('shop.deleteProductImages', $args);
			if(!$output->toBool())
			{
				throw new ShopException($output->getMessage(), $output->getError());
			}
		}
		$this->insertProductImages($product);
		return TRUE;
	}

    /**
     * Set primary image
     * @param $product
     * @throws ShopException
     */
    public function updatePrimaryImage($product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$args->is_primary = "N";
		$output = executeQuery('shop.updatePrimaryImage', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		if(isset($product->primary_image)){
			$args->primary_image = $product->primary_image;
			$args->is_primary = "Y";
			$output = executeQuery('shop.updatePrimaryImage', $args);
			if(!$output->toBool())
			{
				throw new ShopException($output->getMessage(), $output->getError());
			}
		}
	}

    /**
     * Set primary image filename for product
     * @param $product
     * @return bool
     * @throws ShopException
     */
    public function updatePrimaryImageFilename($product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$args->is_primary = "Y";
		$output = executeQuery('shop.getPrimaryImageFilename', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		$args->primary_image_filename = $output->data->filename;
		$output = executeQuery('shop.updatePrimaryImageFilename', $args);
		return TRUE;
	}

    /**
     * Set new primary image for product in case of deletion
     * @param $product
     * @return bool
     * @throws ShopException
     */
    public function setNewPrimaryImage($product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$output = executeQueryArray('shop.getProductImages', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		$args->primary_image_filename = $output->data[0]->filename;
		$args->primary_image = $output->data[0]->image_srl;
		$output = executeQuery('shop.updatePrimaryImageFilename', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		$args->is_primary = 'Y';
		$output = executeQuery('shop.updatePrimaryImage', $args);
		if(!$output->toBool())
		{
			throw new ShopException($output->getMessage(), $output->getError());
		}
		return TRUE;
	}

    /**
     * get maximum price
     * @param $module_srl
     * @param null $category_srl
     * @return int
     */
    public function getMaxPrice($module_srl, $category_srl=null)
    {
        $params = array('module_srl' => $module_srl);
        if ($category_srl) $params['category_srl'] = $category_srl;
        $out = $this->query('getProductsMaxPrice', $params);
        return isset($out->data->max) ? $out->data->max : 0;
    }

    /**
     * get minimum price
     * @param $module_srl
     * @param null $category_srl
     * @return int
     */
    public function getMinPrice($module_srl, $category_srl=null)
    {
        $params = array('module_srl' => $module_srl);
        if ($category_srl) $params['category_srl'] = $category_srl;
        $out = $this->query('getProductsMinPrice', $params);
        return isset($out->data->max) ? $out->data->max : 0;
    }

    /**
     * @param $category_srl
     */
    public function getForCategory($category_srl)
    {

    }
}
