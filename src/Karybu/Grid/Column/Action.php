<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Action extends Column{
    protected $_actions = array();
    public function addAction($id, $action){
        $this->_actions[$id] = $action;
        return $this;
    }
    public function getActions(){
        return $this->_actions;
    }
    public function render($row){
        $result = $this->getConfig('wrapper_top');
        foreach ($this->getActions() as $id=>$action){
            $result .= $action->render($row);
        }
        $result .= $this->getConfig('wrapper_bottom');
        return $result;
    }
    public function getSortable(){
        return false;
    }
}