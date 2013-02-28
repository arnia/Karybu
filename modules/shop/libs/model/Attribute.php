<?php
class Attribute extends BaseItem
{

    public
        $attribute_srl,
        $module_srl,
        $member_srl,
        $title,
        $type,
        $required,
        $status,
        $values = array(),
        $default_value,
        $is_filter,
        $regdate,
        $last_update,
        $category_scope = array();

    /** @var AttributeRepository */
    public $repo;

    /**
     * Persists an attribute
     * @return int|object
     */
    public function save()
    {
        $repo = new AttributeRepository();
        return $repo->insertAttribute($this);
    }

    /**
     * Returns specific attribute type
     * @param $lang
     * @return array
     */
    public function getType($lang)
    {
        $repo = new AttributeRepository();
        return $repo->getTypes($lang, $this->type);
    }

    /**
     * Returns an array of values (from $this->values, which looks like "value1|value2|value3")
     * @param string $delimiter
     * @param bool $trim
     * @return array
     */
    public function getValues($delimiter='|', $trim=true)
    {
        $values = explode($delimiter, $this->values);
        if ($trim) foreach ($values as $i=>$val) $values[$i] = trim($val);
        return $values;
    }

    /**
     * Checks wether the attribute is numeric
     * @return bool
     */
    public function isNumeric()
    {
        $repo = $this->repo; return $this->type == $repo::TYPE_NUMERIC;
    }

    /**
     * Checks wether the attribute is select
     * @return bool
     */
    public function isSelect()
    {
        $repo = $this->repo; return $this->type == $repo::TYPE_SELECT;
    }


    /**
     * Checks wether the attribute is multiple select
     * @return bool
     */
    public function isMultipleSelect()
    {
        $repo = $this->repo; return $this->type == $repo::TYPE_SELECT_MULTIPLE;
    }

    /**
     * Gets attribute min value (comparing all products that have the numeric $attribute associated)
     * @return int|mixed
     */
    public function getMinValue()
    {
        return $this->repo->getAttributeMinValue($this->module_srl, $this->attribute_srl);
    }

    /**
     * Gets attribute max value (comparing all products that have the numeric $attribute associated)
     * @return int|mixed
     */
    public function getMaxValue()
    {
        return $this->repo->getAttributeMaxValue($this->module_srl, $this->attribute_srl);
    }

}