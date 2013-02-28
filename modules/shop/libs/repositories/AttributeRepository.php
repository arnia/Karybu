<?php

/**
 * Handles database operations for Attribute
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class AttributeRepository extends BaseRepository
{
    const
        TYPE_TEXTFIELD = 1,
        TYPE_TEXTAREA = 2,
        TYPE_DATE = 3,
        TYPE_BOOLEAN = 4,
        TYPE_SELECT = 5,
        TYPE_SELECT_MULTIPLE = 6,
        TYPE_NUMERIC = 7;

    /**
     * Returns all types if no $id or returns the type specified by $id.
     *
     * @param $lang
     * @param null $id
     * @return array
     * @throws ShopException
     */
    public function getTypes($lang, $id=null)
    {
        $arr = array(
            self::TYPE_TEXTFIELD       => $lang->types['text_field'],
            self::TYPE_TEXTAREA        => $lang->types['textarea'],
            self::TYPE_DATE            => $lang->types['date'],
            self::TYPE_BOOLEAN         => $lang->types['boolean'],
            self::TYPE_SELECT          => $lang->types['select'],
            self::TYPE_SELECT_MULTIPLE => $lang->types['select_multiple'],
            self::TYPE_NUMERIC         => $lang->types['numeric']
        );
        if (!$id) return $arr;
        if (!array_key_exists($id, $arr)) throw new ShopException('Invalid type');
        return $arr[$id];
    }

	/**
	 * Insert a new attribute; returns the ID of the newly created record
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 * @param $attribute Attribute
	 * @return int
	 */
	public function insertAttribute(Attribute &$attribute)
	{
        if ($attribute->attribute_srl) throw new ShopException('A srl must NOT be specified');
        $attribute->attribute_srl = getNextSequence();
        if(count($attribute->values ) == 0)
        {
            unset($attribute->values);
        }
        else
        {
            $attribute->values = implode('|', array_map('trim', explode('|', $attribute->values)));
        }
		$output = executeQuery('shop.insertAttribute', $attribute);
		self::check($output);
        $this->insertAttributeScope($attribute);
        return $output;
	}

    /**
     * Insert attribute scope (category)
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $attribute Attribute
     * @return boolean
     */
    public function  insertAttributeScope(Attribute &$attribute)
    {
        $args = new stdClass();
        $args->attribute_srl = $attribute->attribute_srl;
        foreach($attribute->category_scope as $category){
            $args->category_srl = $category;
            $output = executeQuery('shop.insertAttributeScope',$args);
            self::check($output);
        }
        return TRUE;
    }


    /**
     * Updates an attribute
     * @author Florin Ercus (dev@xpressengine.org)
     * @param $attribute Attribute
     * @throws Exception
     * @return mixed
     */
    public function updateAttribute(Attribute $attribute)
    {
        if (!$attribute->attribute_srl) throw new ShopException('Target srl must be specified');
        if (count($attribute->values ) == 0)
        {
            unset($attribute->values);
        }
        else
        {
            $attribute->values = implode('|', array_map('trim', explode('|', $attribute->values)));
        }
        $output = executeQuery('shop.updateAttribute', $attribute);
        self::check($output);
        $this->updateAttributeScope($attribute);
        return $output;
    }

    /**
     * Update attribute scope (category)
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $attribute Attribute
     * @return boolean
     */
    public function updateAttributeScope(Attribute &$attribute)
    {
        $this->deleteAttributesScope($attribute);
        $this->insertAttributeScope($attribute);
        return TRUE;
    }


    /**
	 * Deletes one or more attributes by $attribute_srl or $module_srl
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 * @param $args array
	 */
	public function deleteAttributes($args)
	{
        if (!isset($args->attribute_srls)) {
            if (!isset($args->module_srl)) throw new ShopException("Please provide attribute_srls or module_srl.");
            if (!is_array($args->attribute_srls)) throw new ShopException("This query expects an array of attribute srls");
        }
        //delete attributes
		$output = executeQuery('shop.deleteAttributes', $args);
        self::check($output);
        //delete attributes scope
        $output = executeQuery('shop.deleteAttributesScope',$args);
        self::check($output);
        //delete product attributes
		$output = executeQuery('shop.deleteProductAttributes',$args);
		self::check($output);
        return $output;
	}

    /**
     * Delete attribute scope
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $attribute Attribute
     * @return boolean
     */
    public function deleteAttributesScope(Attribute &$attribute)
    {
        $args = new stdClass();
        $args->attribute_srls[] = $attribute->attribute_srl;
        $output = executeQuery('shop.deleteAttributesScope',$args);
        self::check($output);
        return TRUE;
    }

	/**
	 * Retrieve an attribute object from the database given a list of attribute srls.
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 * @param $srls array
	 * @return mixed|array of attribute objects or only one attribute object if count($srl) is 1
	 */
	public function getAttributes(array $srls)
	{
		$args = new stdClass();
		$args->attribute_srls = $srls;
		$output = executeQueryArray('shop.getAttributesBySrls', $args);
		self::check($output);
		$rez = array();
        foreach ($output->data as $data) {
            $rez[$data->attribute_srl] = new Attribute($data);
            $this->getAttributeScope($rez[key($rez)]);
        }
		if( empty($rez)) return false;
		else {
			foreach($rez as $att){
				$att->values  = explode('|',$att->values);
			}
			return $rez;
		}
	}

	/**
	 * Retrieve all value combinations of configurable attributes
	 *
	 * @author Dan Dragan(dev@xpressengine.org)
	 * @param $product array
	 * @return array of combinations
	 */
	public function getValuesCombinations(array $attributes,$i=0)
	{
		if (!isset($attributes[$i])) {
			return array();
		}
		if ($i == count($attributes) - 1) {
			return $attributes[$i];
		}

		// get combinations from subsequent arrays
		$tmp = $this->getValuesCombinations($attributes, $i + 1);

		$result = array();

		// concat each array from tmp with each element from $arrays[$i]
		foreach ($attributes[$i] as $v) {
			foreach ($tmp as $t) {
				$result[] = is_array($t) ?
					array_merge(array($v), $t) :
					array($v, $t);
			}
		}

		return $result;
	}

    /**
     * Retrieve attribute Scope
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $attribute Attribute
     * @return boolean
     */
    public function getAttributeScope(Attribute &$attribute)
    {
        $args = new stdClass();
        $args->attribute_srl = $attribute->attribute_srl;
        $output = executeQueryArray('shop.getAttributeScope',$args);
        self::check($output);
        foreach($output->data as $scope){
            $attribute->category_scope[] = $scope->category_srl;
        }
        return TRUE;
    }

    /**
     * Retrieve a list of Attributes object from the database by modul_srl
     * @author Florin Ercus (dev@xpressengine.org)
     * @param $module_srl int
     * @param $extraArgs array
     * @return Attribute list
     */
    public function getAttributesList($module_srl, array $extraArgs=null)
    {
        if (!is_numeric($module_srl)) throw new ShopException('module_srl must be a valid int');
        $params = array(
            'page' => $page = (Context::get('page') ? Context::get('page') : 1),
            'module_srl' => $module_srl
        );
        if ($extraArgs) $params = array_merge($params, $extraArgs);
        Context::set('page', $page);
        $output = $this->query('getAttributesList', $params);
        $attributes = array();
        foreach ($output->data as $properties) {
            $o = new Attribute($properties);
            $this->getAttributeScope($o);
            $attributes[] = $o;
        }
        $output->attributes = $attributes;
        return $output;
    }

    /**
     * Add attributes info to export folder
     * @author Dan Dragan (dev@xpressengine.org)
     *
     * @param array $attributes
     *
     * @return boolean
     */
    public function addAttributesToExportFolder($attributes)
    {
        $buff = '';
        //table header for attributes csv
        foreach($attributes[0] as $key => $value)
        {
            if(!in_array($key,array('member_srl','module_srl','regdate','last_update','repo','cache')))
            {
                if($key == 'attribute_srl') $buff = $buff.'id,';
                else $buff = $buff.$key.",";
            }
        }
        $buff = $buff."\r\n";
        //table values  for products  csv
        foreach($attributes as $attribute){
            foreach($attribute as $key => $value){
                if(!in_array($key,array('member_srl','module_srl','regdate','last_update','repo','category_scope','cache')))
                {
                    $buff = $buff.$value.",";
                }
                $category_scope = '';
                if($key == 'category_scope'){
                    foreach($value as $category){
                        if($category_scope == '') $category_scope = $category;
                        else $category_scope = $category_scope.'|'.$category;
                    }
                    $buff = $buff.$category_scope.",";
                }
            }
            $buff = $buff."\r\n";
        }
        $attribute_csv_filename = 'attributes.csv';
        $attribute_csv_path = sprintf('./files/attach/shop/export-import/%s', $attribute_csv_filename);
        FileHandler::writeFile($attribute_csv_path, $buff);

        return TRUE;
    }

    /**
     * import attributes from import folder
     * @author Dan Dragan (dev@xpressengine.org)
     *
     * @param $args for module_srl and member_srl
     *
     * @return  boolean
     */
    public function insertAttributesFromImportFolder($params)
    {
        if(file_exists('./files/attach/shop/export-import/attributes.csv')) {
            $csvString = file_get_contents('./files/attach/shop/export-import/attributes.csv');
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
                    $attributes[] = $args;
                    unset($args);
                }
            }

            foreach($attributes as $attribute){
                $category_scope = explode('|',$attribute->category_scope);
                unset($attribute->category_scope);
                foreach($category_scope as $scope){
                    if(isset($params->category_ids[$scope]))$attribute->category_scope[] = $params->category_ids[$scope];
                }

                $att = new Attribute($attribute);
                $att->module_srl = $params->module_srl;
                $att->member_srl = $params->member_srl;
                $att->attribute_srl_srl = $this->insertAttribute($att);
                $attribute_ids[$attribute->id] = $att->attribute_srl;
                $oAttributes[] = $att;
            }
            return $attribute_ids;
        }   else return NULL;

    }

    /**
     * Retrieve a list of configurable Attributes object from the database by modul_srl
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $module_srl int
     * @return Attribute list
     */
    public function getConfigurableAttributesList($module_srl)
    {
        if (!is_numeric($module_srl)) throw new ShopException('module_srl must be a valid int');
        $args = new stdClass();
        $args->module_srl = $module_srl;
        if (!isset($args->module_srl)) throw new ShopException("Missing arguments for attributes list : please provide module_srl");
        $args->type = $this::TYPE_SELECT;

        $output = executeQueryArray('shop.getAttributesList', $args);
        $attributes = array();
        foreach ($output->data as $properties) {
            $o = new Attribute($properties);
            $attributes[] = $o;
        }
        $output->attributes = $attributes;
        return $output;
    }

    /**
     * Returns Attributes displayable for a specific category / set of categories, or for no category.
     *
     * @param $module_srl
     * @param array $category_srls
     * @param bool $withParents takes categories parents into account
     * @return mixed
     * @throws ShopException
     */
    public function getFilterAttributes($module_srl, array $category_srls=null, $withParents=true)
    {
        if (!is_numeric($module_srl)) throw new ShopException('module_srl must be a valid natural number');
        $catSerials = array();
        if ($withParents && !empty($category_srls)) {
            $cRepo = new CategoryRepository();
            $paths = $cRepo->getCategoryPaths($category_srls);
            foreach ($paths as $srl=>$path) $catSerials = array_merge($catSerials, $path, array($srl));
        }
        else $catSerials = $category_srls;
        $params = array('module_srl' => $module_srl);
        if (!empty($catSerials)) $params['category_srls'] = $catSerials;
        $aRepo = new AttributeRepository();
        $attributes = $aRepo->get(null, 'getCategoriesAttributeFilters', null, $params);
        return $attributes;
    }

    /**
     * Returns the minimum value for a numeric $attribute_srl (from all products having $attribute values associated)
     *
     * @param $module_srl
     * @param $attribute_srl
     * @return int|mixed
     */
    public function getAttributeMinValue($module_srl, $attribute_srl)
    {
        $values2 = $this->query('getAttributeMinValue', array('attribute_srl'=>$attribute_srl), true)->data;
        $values = array(); foreach ($values2 as $i=>$v) $values[$i] = $v->value;
        if (empty($values)) return 0;
        $min = reset($values);
        foreach ($values as $v) if ($v < $min) $min = $v;
        return $min;
    }

    /**
     * Returns the max value for a numeric $attribute_srl (from all products having $attribute values associated)
     *
     * @param $module_srl
     * @param $attribute_srl
     * @return int|mixed
     */
    public function getAttributeMaxValue($module_srl, $attribute_srl)
    {
        $values2 = $this->query('getAttributeMaxValue', array('attribute_srl'=>$attribute_srl), true)->data;
        $values = array(); foreach ($values2 as $i=>$v) $values[$i] = $v->value;
        if (empty($values)) return 0;
        $max = reset($values);
        foreach ($values as $v) if ($v > $max) $max = $v;
        return $max;
    }



}