<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Checkbox extends Text
{
    public function render($row)
    {
        $prefix = $suffix = '';
        if ($checkboxKey = $this->getConfig('checkbox')){

            $name = $this->getConfig('name');
            $title = $this->getConfig('title');
            $value = $this->getConfig('value');
            $val = htmlspecialchars($row->$value);
            if ($cond = $this->getConfig('cond')){
                $condition = $row->$cond;
                if($condition){
                    $checked =  ' checked="checked" ';
                }else{
                    $checked = '';
                }
            }
            $prefix = '<input type="checkbox" name="'.$name.'" title="'.$title.'" value="'.$val.'"'.$checked.'/>';

            $suffix = '';
        }
        return $prefix.parent::render($row).$suffix;
    }
}