<?php
namespace Karybu\Grid\Column;
use Karybu\Grid\Column;

class Factory{
    public static function getColumnClassName($type){
        $type = ucfirst($type);
        return '\Karybu\Grid\Column\\'.$type;
    }
}