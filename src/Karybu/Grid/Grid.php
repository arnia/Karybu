<?php
namespace Karybu\Grid;
use Karybu\Grid\Column\Factory;
use Karybu\Grid\Column\Text;

class Grid{
    protected $_columns = array();
    protected $_rows = array();
    protected $_allowMassSelect = true;
    protected $_massSelectName = 'mass_select';
    protected $_cssClasses = array();

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
        $this->_allowMassSelect = $flag;
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
}