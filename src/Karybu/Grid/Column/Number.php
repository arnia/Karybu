<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Number extends Column{
    protected  function _getValue($row){
        return number_format(parent::_getValue($row));
    }
}