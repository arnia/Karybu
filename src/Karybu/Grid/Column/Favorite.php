<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Favorite extends Column{
    public function render($row){
        global $lang;
        if (empty($row->admin_index_act)){
            return '';
        }
        $selected = $this->getConfig('selected', array());
        if (in_array($row->module, $selected)){
            $class = 'fvOn';
            $message = $lang->on;
        }
        else{
            $class = 'fvOff';
            $message = $lang->off;
        }

        $html = '<button type="button" class="'.$class.'" id="fav_star_'.$row->module.'" onclick="doToggleFavoriteModule(this, \''.$row->module.'\'); return false;">';
        $html .= $lang->favorite.$message;
        $html .= '</button>';
        return $html;
    }
    public function getSortable(){
        return false;
    }
}