<?php
namespace Karybu\Grid\Action;
use Karybu\Grid\Action\Action;
class Confirm extends Action{
    public function render($row){
        $jsAction = $this->getConfig('js_action');
        if (!$jsAction){
            return parent::render($row);
        }
        $params = $this->getConfig('params');
        $onclick = $jsAction.'(';
        foreach ($params as $key=>$value){
            $onclick .= "'".$row->$value."', ";
        }
        $onclick .= "'".$this->getConfig('confirm')."'".');return false';
        $html = '<a title="'.$this->getConfig('title').'" data-toggle="tooltip" href="#" onclick="'.$onclick.'"><i class="'.$this->getConfig('icon_class').'">'.$this->getConfig('title').'</i></a>';
        return $html;
    }
}