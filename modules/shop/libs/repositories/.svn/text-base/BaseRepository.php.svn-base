<?php
abstract class BaseRepository
{
    public $entity;
    /** @var ShopCache */
    public static $cache;

    public function __construct()
    {
        $this->entity = $this->getEntityName();
		if(is_null(self::$cache))
		{
			self::$cache = new ShopCache();
		}
    }

    protected function getEntityName()
    {
        return str_replace('Repository', '', get_class($this));
    }

    public function query($name, $params = null, $asArray=false)
    {
        if (!$params) $params = array();
        if ($params instanceof BaseItem) $params = get_object_vars($params);
        if (!is_array($params) && !($params instanceof stdClass)) throw new ShopException('Wrong $params type');
        if (!strpos($name, '.')) $name = "shop.$name";
        $name = str_replace('%E', $this->entity, $name);
        if ($params) $params = (object) $params;
        $function = 'executeQuery' . ($asArray ? 'Array' : '');
        $output = $function($name, $params);
        if ((is_string($asArray) || is_array($asArray)) && !empty($output->data)) {
            self::rowsToEntities($output->data, $asArray);
        }
        if ($output->getMessage() == 'Specified query ID value is invalid.') {
            $output->setMessage("Query not found: $name");
        }
        return self::check($output);
    }

    public static function check($output)
    {
        if (!is_object($output)) {
            throw new ShopException('A valid query output is expected here');
        }
        if (!$output->toBool()) {
            throw new DbQueryException($output->getMessage(), $output->getError());
        }
        return $output;
    }

    /**
     * Mass CRUD operations
     * mass update is not (yet?) supported in XE
     */

    /**
     * examples:
     * $categoryRepo->get(1405) //if exists, returns a single Category object corresponding for srl 1405. else null
     * $categoryRepo->get(array(1405, 7)) //returns an array of category objects
     * $categoryRepo->get() //returns all categories
     * $categoryRepo->get(null, 'getCatCustom', 'CustomCategory', array('type'=>CustomCategory::TYPE_SOMETHING)) //returns CustomCategory objects from custom query with 1 custom arg
     * @param mixed  $srls A single numeric srl or an array of srls
     * @param string $query Query to use
     * @param null   $entity Return entity
     *
     * @return mixed If $srls was a single numeric, the result should be an object of type $this->entity or $fetchAs. Else we'll return an array of objects corresponding to the $srls array
     * @throws Exception
     */
    public function get($srls=null, $query="get%E", $entity=null, array $extraParams=array())
    {
        $single = false;
        if (is_numeric($srls)) {
            $single = true;
            $srls = array($srls);
        }
        if (!$entity) $entity = $this->entity;
        if (!class_exists($entity)) throw new ShopException("Class '$entity' doesn't exist");
        $params = ($srls ? array_merge(array('srls' => $srls), $extraParams) : $extraParams);
        $output = $this->query($query, $params, true);
        self::rowsToEntities($output->data, $entity);
        if ($single && count($output->data) > 1) {
            throw new ShopException('Got more than 1 result, expected 1');
        }
        return $single && count($output->data) == 1 ? $output->data[0] : $output->data;
    }

    /**
     * @param array $data
     * @param       $entity string|array if string, each row will be replaced with a single $entity object populated with row data. If array of $prefix=>$entityName, each row will be replaced with an array of objects of $entityName types populated based on $prefixes associated to them.
     *
     * TODO: test array $entity
     *
     * @throws Exception
     */
    protected static function rowsToEntities(array &$data, $entity, $prefixDelimiter='.')
    {
        if (!is_string($entity) && !is_array($entity)) throw new ShopException('invalid input');
        foreach ($data as $i=>$row) {
            if (is_string($entity)) $data[$i] = new $entity($row);
            else {
                $rowObjects = array();
                foreach ($entity as $prefix=>$entityName) {
                    if (!is_string($entityName)) throw new ShopException('Invalid input');
                    $entityData = array();
                    foreach ($row as $col=>$val) {
                        $prefixBlock = $prefix.$prefixDelimiter;
                        if (substr($col, 0, strlen($prefixBlock)) === $prefixBlock) {
                            $entityData[substr($col, strlen($prefixBlock) + 1, strlen($col))] = $val;
                        }
                    }
                    $rowObjects[] = new $entityName($entityData);
                }
                $data[$i] = $rowObjects;
            }
        }
    }

    /**
     * @param mixed  $srls  A single numeric srl or an array of srls
     * @param string $query Query to use
     *
     * @return object
     * @throws Exception
     */
    public function delete($srls, $query="delete%Es")
    {
        if (is_numeric($srls)) {
            $srls = array($srls);
        } elseif (!is_array($srls)) throw new ShopException('Invalid $srls input');
        return $this->query($query, array('srls'=>$srls));
    }

    public function count($query="count%Es", array $extraParams=array())
    {
        return $this->query($query, $extraParams)->data->count;
    }

    public function getList($query='list%Es', $page=null, array $params=array(), $entity = null)
    {
        $params['page'] = ($page ? $page : 1);
        $entity = ($entity ? $entity : $this->entity);
        if (!class_exists($entity)) throw new ShopException("Class $entity doesn't exist");
        $output = $this->query($query, $params, $entity);
        return $output;
    }

    public static function vid()
    {
        /** @var $oModuleModel moduleModel */
        $siteModuleInfo = Context::get('site_module_info');
        return $siteModuleInfo->domain;
    }

}