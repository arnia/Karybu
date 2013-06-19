<?php
namespace Karybu\Grid;
use Karybu\Grid\Column\Factory;
use Karybu\Grid\Column\Text;

class Grid{
    const DEFAULT_SORT_ORDER = 'asc';
    protected $_columns = array();
    protected $_rows = array();
    protected $_allowMassSelect = true;
    protected $_massSelectName = 'mass_select';
    protected $_cssClasses = array();
    protected $_sortParams = array();
    protected $_sortIndex = null;
    protected $_sortOrder = null;
    protected $_showOrderNumberColumn = false;
    protected $_massSelectAttributes = array();
    protected $_massSelectClass = '';
    protected $_id = null;
    public function setId($id){
        $this->_id = $id;
        return $this;
    }
    public function getId(){
        return $this->_id;
    }

    /**
     * add column to grid
     * @param $id
     * @param Column $column
     * @return $this
     */
    public function addColumn($id, $type, $column) {
        if (is_array($column)){
            $columnClass = Factory::getColumnClassName($type);
            $column = new $columnClass($column);
        }
        $column->setGrid($this);
        $this->_columns[$id] = $column;
        return $this;
    }

    /**
     * get the grid columns
     * @return array
     */
    public function getColumns() {
        return $this->_columns;
    }

    /**
     * remove a column
     * @param $id
     * @return $this
     */
    public function removeColumn($id) {
        if (isset($this->_columns[$id])){
            unset($this->_columns[$id]);
        }
        return $this;
    }
    public function setRows($rows){
        $this->_rows = $rows;
        return $this;
    }
    public function getRows(){
        return $this->_rows;
    }
    public function setAllowMassSelect($flag){
        $this->_allowMassSelect = (bool)$flag;
        return $this;
    }
    public function getAllowMassSelect(){
        return $this->_allowMassSelect;
    }
    public function addCssClass($class){
        $parts = explode(' ', $class);
        foreach ($parts as $part) {
            $this->_cssClasses[trim($part)] = 1;
        }
        return $this;
    }
    public function getCssClasses($asString = true){
        if ($asString){
            return implode(' ', array_keys($this->_cssClasses));
        }
        return $this->_cssClasses;
    }
    public function getColumn($id){
        if (isset($this->_columns[$id])){
            return $this->_columns[$id];
        }
        return null;
    }
    public function setMassSelectName($name){
        $this->_massSelectName = $name;
        return $this;
    }
    public function getMassSelectName(){
        return $this->_massSelectName;
    }
    public function setSortParams(array $params){
        $this->_sortParams = $params;
        return $this;
    }
    public function addSortParams(array $params){
        $this->_sortParams = array_merge($this->_sortParams, $params);
        return $this;
    }
    public function getSortParams(){
        return $this->_sortParams;
    }
    public function setSortIndex($index){
        foreach ($this->getColumns() as $column){
            if ($column->getSortIndex() == $index && $column->getSortable()){
                $this->_sortIndex = $index;
                break;
            }
        }
        return $this;
    }
    public function getSortIndex(){
        return $this->_sortIndex;
    }
    public function setSortOrder($order){
        $order = strtolower($order);
        if (in_array($order, array('asc', 'desc'))){
            $this->_sortOrder = $order;
        }
        else{
            $this->_sortOrder = self::DEFAULT_SORT_ORDER;
        }
        return $this;
    }
    public function getSortOrder(){
        return $this->_sortOrder;
    }
    public function setShowOrderNumberColumn($flag){
        $this->_showOrderNumberColumn = (bool)$flag;
        return $this;
    }
    public function getShowOrderNumberColumn(){
        return $this->_showOrderNumberColumn;
    }
    public function setMassSelectAttributes(array $attributes){
        $this->_massSelectAttributes = $attributes;
        return $this;
    }
    public function getMassSelectAttributes(){
        return $this->_massSelectAttributes;
    }
    public function renderMassSelectAttributes($row){
        $values = array();
        foreach ($this->getMassSelectAttributes() as $key=>$attribute){
            $value = isset($row->$attribute) ? $row->$attribute : '';
            $values[] = $key.'="'.$value.'"';
        }
        if (count($values) > 0){
            return ' '.implode(' ', $values);
        }
        return '';
    }
    public function setMassSelectClass($class){
        $this->_massSelectClass = $class;
        return $this;
    }
    public function getMassSelectClass($forHtml = true){
        $class = $this->_massSelectClass;
        if (!$class){
            return '';
        }
        $prefix = $suffix = '';
        if ($forHtml){
            $prefix = ' class="';
            $suffix = '"';
        }
        return $prefix.$class.$suffix;
    }
}