<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Options extends Column{
    const WILDCARD = '*';
    public function _getValue($row){
        $value = parent::_getValue($row);
        $options = $this->getConfig('options');
        //check for label in available options
        if (is_array($options) && isset($options[$value])){
            return $options[$value];
        }
        if (isset($options[self::WILDCARD])){
            return $options[self::WILDCARD];
        }
        //if no label is found check if we can show the key
        if ($this->getConfig('show_raw_value')){
            return $value;
        }
        return '';
    }
}