<?php
abstract class BaseItem
{
    /** @var BaseRepository */
    public $repo;
    /** @var ShopCache */
    public static $cache;

    protected $meta = array();

	public function __construct($data = NULL)
	{
		if (!is_null($data) && !is_numeric($data) && !is_array($data) && !($data instanceof stdClass)) {
            throw new ShopException('Invalid $data type');
        }
		if (is_array($data) || $data instanceof stdClass) {
			$this->loadFromArray((array)$data);
		}

        /**
         * Look for Item repository.
         * For IDE purposes like code completion $this->repo's type should be hinted in each Item the way I did in Cart.
         */
        $repoClass = $this->getRepo();
        $reflection = new ReflectionClass($repoClass);
        $repoClass = (string) $reflection->getName();
        $this->repo = ( $reflection->isInstantiable() ? new $repoClass : null );

        /**
         * Look for srl field if it's not already set in class $meta
         */
        $reflection = new ReflectionClass($this);
        if ($srlField = $this->getMeta('srl')) { //if srl is given
            if (!$reflection->hasProperty($srlField)) {
                throw new ShopException("Srl field '$srlField' doesn't exist");
            }
        }
        elseif ($reflection->hasProperty('srl')) {
            $this->setMeta('srl', 'srl');
        }
        else { //else return the first _srl field
            foreach ($this as $field => $value) {
                if (substr($field, strlen($field) - 4, strlen($field)) === '_srl') {
                    $this->setMeta('srl', $field);
                    break;
                }
            }
            if (!$this->getMeta('srl')) {
                throw new ShopException('Couldn\'t identify the _srl column');
            }
        }

        //data can also be a serial
        if (is_numeric($data)) {
            if ($obj = $this->repo->get($data, "get%E", get_called_class())) {
                $this->copy($obj);
            }
            else throw new ShopException("No such {$this->repo->entity} srl $data");
        }

		if(is_null(self::$cache))
		{
			self::$cache = new ShopCache();
		}

    }

    public function copy(BaseItem $o)
    {
        foreach ($this as $field=>$value) {
            if (property_exists($o, $field)) {
                $this->$field = $o->$field;
            }
        }
    }

    protected function getRepo()
    {
        return $this->getMeta('repo') ? $this->getMeta('repo') : get_called_class() . 'Repository';
    }

	protected function loadFromArray(array $data)
	{
		foreach ($data as $field => $value)
		{
			if (property_exists(get_called_class(), $field)) {
				$this->$field = $value;
                unset($data[$field]);
			}
		}
        //as for the rest...
        foreach ($data as $field => $value) {
            $this->setMeta($field, $value);
        }
	}

    public function query($name, $params = null, $array = false)
    {
        if (!isset($this->repo)) {
            throw new ShopException(get_called_class() . " doesn't have a repository.");
        }
        return $this->repo->query($name, $params, $array);
    }

    /**
     * Checks if object is new or exists already in the database
     *
     * @return bool
     */
    public function isPersisted($checkDB=false)
    {
        $srl = $this->getMeta('srl');
        if (!is_numeric($this->$srl)) return false;
        if ($checkDB) {
            $object = $this->repo->get($this->$srl);
            if (!$object) return false;
        }
        return true;
    }

    public function getMeta($key)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }

    public function setMeta($key, $val)
    {
        $this->meta[$key] = $val;
    }


    /**
     * CRUD
     * You still have to create the queries. %E gets replaced by entity name.
     */

    public function save()
    {
        return $this->isPersisted() ? $this->update() : $this->insert();
    }

    private function addPrivatesToParams(&$params)
    {
        if (is_array($privates = $this->getMeta('privates'))) {
            //we have properties that are protected from normal get/set (wrapped in magic methods)
            foreach ($privates as $private) {
                if ($this->$private !== null && !isset($params[$private])) {
                    $params[$private] = $this->$private;
                }
            }
        }
    }

    public function update($query='update%E')
    {
        $entity = get_called_class();
        if (!$this->isPersisted()) throw new ShopException("No $entity srl for update");
        $params = get_object_vars($this);
        $this->addPrivatesToParams($params);
        return $this->query($query, $params);
    }

    public function insert($query='insert%E')
    {
        $entity = get_called_class();
        if ($this->isPersisted()) throw new ShopException("$entity already persisted, can't insert.");
        $srl = $this->getMeta('srl');
        $this->$srl = getNextSequence();
        $params = get_object_vars($this);
        return $this->query($query, $params);
    }

    public function delete($query='delete%Es')
    {
        $entity = get_called_class();
        $srl = $this->getMeta('srl');
        if (!$this->isPersisted()) throw new ShopException("$entity not persisted, can't delete.");
        $this->query($query, array('srls' => array($this->$srl)));
    }

}