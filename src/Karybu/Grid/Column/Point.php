<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;
class Point extends Column{
    public function render($row){
        global $lang;
        $html = '';
        $html = '<form action="./" method="get" class="form-inline">';
        $html .= '<input type="hidden" name="module" value="point" />';
        $html .= '<input type="hidden" name="member_srl" value="'.$row->member_srl.'" />';
        $html .= '<input type="text" name="orgpoint" value="'.$row->point.'" disabled="disabled" style="width:40px;text-align:right" />';
        $html .= '<input type="text" id="point_'.$row->member_srl.'" name="point" style="width:40px;text-align:right" />';
        $html .= '<input type="button" value="'.$lang->cmd_update.'" onclick="updatePoint('.$row->member_srl.')" class="btn" />';
        $html .='</form>';
        return $html;
    }
}