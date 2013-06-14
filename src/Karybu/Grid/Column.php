<?php
namespace Karybu\Grid;
abstract class Column {
    /**
     * @var array
     */
    protected $_config;
    /**
     * @var Grid
     */
    protected $_grid;

    /**
     * constructor
     * @param array $config
     */
    public function __construct(array $config){
        $this->_config = $config;
    }

    /**
     * get config settings
     * @param null $setting
     * @return mixed
     */
    public function getConfig($setting = null){
        if (is_null($setting)){
            return $this->_config;
        }
        if(isset($this->_config[$setting])){
            return $this->_config[$setting];
        }
        return null;
    }

    /**
     * get the row value for this column
     * @param stdClass $row
     * @return string
     */
    protected function _getValue($row){
        $index = $this->getConfig('index');
        if (is_null($index)){
            return '';
        }
        if (isset($row->$index)){
            if ($this->getConfig('masked')){
                return getEncodeEmailAddress($row->$index);
            }
            return $row->$index;
        }
        return '';
    }

    /**
     * render the column for a row - wrapper for _getValue()
     * @param stdClass $row
     * @return string
     */
    public function render($row){
        $prefix = $suffix = '';
        if ($this->getConfig('masked')){
            $prefix = '<span class="masked">';
            $suffix = '</span>';
        }
        if ($this->getConfig('tooltip')){
            $tooltipKey = $this->getConfig('tooltip_key');
            if (!empty($row->$tooltipKey)){
                $prefix = '<div data-toggle="tooltip" data-container=".easyList" title="'.$row->$tooltipKey.'">'.$prefix;
                $suffix .= '</div>';
            }
        }
        return $prefix.$this->_getValue($row).$suffix;
    }

    /**
     * check if the column is sortable
     * @return bool
     */
    public function getSortable(){
        return $this->getConfig('sortable') !== false;
    }

    /**
     * attach a grid object to the column
     * @param Grid $grid
     * @return $this
     */
    public function setGrid(Grid $grid){
        $this->_grid = $grid;
        return $this;
    }

    /**
     * get the column grid
     * @return Grid
     */
    public function getGrid(){
        return $this->_grid;
    }

    /**
     * get the sort url
     * @return string
     */
    public function getSortUrl(){
        if (!$this->getSortable()){
            return '#';
        }
        $sortParams = $this->getGrid()->getSortParams();
        $currentSortOrder = $this->getGrid()->getSortOrder();
        $currentSortIndex = $this->getGrid()->getSortIndex();
        $sortOrder = 'asc';
        if ($currentSortIndex == $this->getSortIndex()){
            if ($currentSortOrder == 'asc'){
                $sortOrder = 'desc';
            }
        }
        $urlParams = array();
        $sortParams = array_merge($sortParams, array(
            'sort_index'=>$this->getSortIndex(),
            'sort_order'=>$sortOrder
        ));
        foreach ($sortParams as $key=>$param){
            $urlParams[] = $key;
            $urlParams[] = $param;
        }
        return call_user_func(array('\Context', 'getUrl'), count($urlParams), $urlParams);
    }
    public function getSortIndex(){
        if ($sortIndex = $this->getConfig('sort_index')){
            return $sortIndex;
        }
        return $this->getConfig('index');
    }

}