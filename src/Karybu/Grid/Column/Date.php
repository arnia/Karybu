<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Date extends Column{
    const DEFAULT_FORMAT = 'Y-m-d';
    public function getFormat(){
        $format = $this->getConfig('format');
        if (empty($format)){
            $format = self::DEFAULT_FORMAT;
        }
        return $format;
    }
    public function _getValue($row){
        $value = parent::_getValue($row);
        if (empty($value)) {
            return '';
        }
        return zdate($value, $this->getFormat());
    }

}