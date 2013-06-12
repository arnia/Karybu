<?php
namespace Karybu\Grid;
abstract class Column {
    protected $_config;
    public function __construct(array $config){
        $this->_config = $config;
    }
    public function getConfig($setting = null){
        if (is_null($setting)){
            return $this->_config;
        }
        if(isset($this->_config[$setting])){
            return $this->_config[$setting];
        }
        return null;
    }
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
    public function render($row){
        $prefix = $suffix = '';
        if ($this->getConfig('masked')){
            $prefix = '<span class="masked">';
            $suffix = '</span>';
        }
        return $prefix.$this->_getValue($row).$suffix;
    }
}