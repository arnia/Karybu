<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Text extends Column{
    protected function _getValue($row){
        $value = parent::_getValue($row);
        if ($length = $this->getConfig('length')){
            $value = cut_str(trim(strip_tags($value)), $length, '...');
        }
        return $value;
    }
}