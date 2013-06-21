<?php
namespace Karybu\Grid;
use Karybu\Grid\Column\Factory;
use Karybu\Grid\Column\Text;

class Grid{
    /**
     * default sort order of the grid elements
     */
    const DEFAULT_SORT_ORDER = 'asc';
    /**
     * columns in the grid
     * @var array
     */
    protected $_columns                 = array();
    /**
     * rows displayed in the grid
     * @var array
     */
    protected $_rows                    = array();
    /**
     * flag to allow mass select checkbox
     * @var bool
     */
    protected $_allowMassSelect         = true;
    /**
     * name of the mass select checkbox
     * @var string
     */
    protected $_massSelectName          = 'mass_select';
    /**
     * additional attributes on the mass select checkbox
     * @var array
     */
    protected $_massSelectAttributes    = array();
    /**
     * class name for the mass select checkbox
     * @var string
     */
    protected $_massSelectClass         = '';
    /**
     * css classes on the grid table wrapper
     * @var array
     */
    protected $_cssClasses              = array();
    /**
     * index of the sorted column
     * @var null
     */
    protected $_sortIndex               = null;
    /**
     * sort direction
     * @var null
     */
    protected $_sortOrder               = null;
    /**
     * additional sort params
     * @var array
     */
    protected $_sortParams              = array();
    /**
     * flag to show a column with the number of the row
     * @var bool
     */
    protected $_showOrderNumberColumn   = false;
    /**
     * id of the table container
     * @var null
     */
    protected $_id                      = null;
    /**
     * current page displayed
     * @var null
     */
    protected $_currentPage             = null;
    /**
     * total number of pages
     * @var null
     */
    protected $_totalPages              = null;
    /**
     * total count
     * @var null
     */
    protected $_totalCount              = null;

    /**
     * set the id of the grid table
     * @param $id
     * @return $this
     */
    public function setId($id){
        $this->_id = $id;
        return $this;
    }

    /**
     * get the id of the grid table
     * @return null|string
     */
    public function getId(){
        return $this->_id;
    }

    /**
     * add column to grid
     * @param string $id
     * @param string $type
     * @param Column|array $column
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
     * @param string $id
     * @return $this
     */
    public function removeColumn($id) {
        if (isset($this->_columns[$id])){
            unset($this->_columns[$id]);
        }
        return $this;
    }

    /**
     * set the rows of the grid
     * @param array $rows
     * @return $this
     */
    public function setRows($rows){
        $this->_rows = $rows;
        return $this;
    }

    /**
     * get the rows of the grid
     * @return array
     */
    public function getRows(){
        return $this->_rows;
    }

    /**
     * set flag for mass select
     * @param bool $flag
     * @return $this
     */
    public function setAllowMassSelect($flag){
        $this->_allowMassSelect = (bool)$flag;
        return $this;
    }

    /**
     * get flag for mass select
     * @return bool
     */
    public function getAllowMassSelect(){
        return $this->_allowMassSelect;
    }

    /**
     * add css class to grid table
     * @param string $class
     * @return $this
     */
    public function addCssClass($class){
        $parts = explode(' ', $class);
        foreach ($parts as $part) {
            $this->_cssClasses[trim($part)] = 1;
        }
        return $this;
    }

    /**
     * get css classes of the grid table
     * @param bool $asString
     * @return array|string
     */
    public function getCssClasses($asString = true){
        if ($asString){
            return implode(' ', array_keys($this->_cssClasses));
        }
        return $this->_cssClasses;
    }

    /**
     * get a specific column
     * @param string $id
     * @return Column|null
     */
    public function getColumn($id){
        if (isset($this->_columns[$id])){
            return $this->_columns[$id];
        }
        return null;
    }

    /**
     * set the name of the mass select checkbox
     * @param string $name
     * @return $this
     */
    public function setMassSelectName($name){
        $this->_massSelectName = $name;
        return $this;
    }

    /**
     * get the mass select checkbox name
     * @return string
     */
    public function getMassSelectName(){
        return $this->_massSelectName;
    }

    /**
     * set the sort intex
     * @param string $index
     * @return $this
     */
    public function setSortIndex($index){
        foreach ($this->getColumns() as $column){
            if ($column->getSortIndex() == $index && $column->getSortable()){
                $this->_sortIndex = $index;
                break;
            }
        }
        return $this;
    }

    /**
     * get the grid sort index
     * @return null|string
     */
    public function getSortIndex(){
        return $this->_sortIndex;
    }

    /**
     * set the grid sort order
     * @param string $order
     * @return $this
     */
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

    /**
     * get the grid sort order
     * @return null|string
     */
    public function getSortOrder(){
        return $this->_sortOrder;
    }

    /**
     * set additional sort params
     * @param array $params
     * @return $this
     */
    public function setSortParams(array $params){
        $this->_sortParams = $params;
        return $this;
    }

    /**
     * add sort param
     * @param array $params
     * @return $this
     */
    public function addSortParams(array $params){
        $this->_sortParams = array_merge($this->_sortParams, $params);
        return $this;
    }

    /**
     * get the additional sort params
     * @return array
     */
    public function getSortParams(){
        return $this->_sortParams;
    }
    /**
     * set flag for showing the number of the row
     * @param bool $flag
     * @return $this
     */
    public function setShowOrderNumberColumn($flag){
        $this->_showOrderNumberColumn = (bool)$flag;
        return $this;
    }

    /**
     * get the flag for showing the number of the row
     * @return bool
     */
    public function getShowOrderNumberColumn(){
        return $this->_showOrderNumberColumn;
    }

    /**
     * set the additional attribute for mass select
     * @param array $attributes
     * @return $this
     */
    public function setMassSelectAttributes(array $attributes){
        $this->_massSelectAttributes = $attributes;
        return $this;
    }

    /**
     * get the attributes for the mass select checkbox
     * @return array
     */
    public function getMassSelectAttributes(){
        return $this->_massSelectAttributes;
    }

    /**
     * render the mass select attributes
     * @param stdClass $row
     * @return string
     */
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

    /**
     * set the mass select class name
     * @param string $class
     * @return $this
     */
    public function setMassSelectClass($class){
        $this->_massSelectClass = $class;
        return $this;
    }

    /**
     * get the mass select class name
     * @param bool $forHtml
     * @return string
     */
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

    /**
     * set current page
     * @param string $page
     * @return $this
     */
    public function setCurrentPage($page){
        $this->_currentPage = $page;
        return $this;
    }
    /**
     * get the current page
     * @return null|string
     */
    public function getCurrentPage(){
        return !is_null($this->_currentPage) ? number_format($this->_currentPage) : null;
    }
    /**
     * set total pages
     * @param string $total
     * @return $this
     */
    public function setTotalPages($total){
        $this->_totalPages = $total;
        return $this;
    }
    /**
     * get the total number of pages
     * @return null|string
     */
    public function getTotalPages(){
        return !is_null($this->_totalPages) ? number_format($this->_totalPages) : null;
    }
    /**
     * set the total count
     * @param $total
     * @return $this
     */
    public function setTotalCount($total){
        $this->_totalCount = $total;
        return $this;
    }
    /**
     * get the total number of records
     * @return null|string
     */
    public function getTotalCount(){
        return !is_null($this->_totalCount) ? number_format($this->_totalCount) : null;
    }
}
