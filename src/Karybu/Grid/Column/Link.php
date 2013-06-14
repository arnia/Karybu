<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Link extends Text{
    public function render($row){
        $prefix = $suffix = '';
        if ($linkKey = $this->getConfig('link_key')){
            if (!empty($row->$linkKey)){
                $link = $row->$linkKey;
            }
            else{
                $link = '#';
            }
            $_target = '';
            if ($target = $this->getConfig('target')){
                $_target = ' target="'.$target.'"';
            }
            if ($link!='#' || !$this->getConfig('hide_on_empty')){
                $prefix = '<a'.$_target.' href="'.$link.'">';
                $suffix = '</a>';
            }
        }
        return $prefix.parent::render($row).$suffix;
    }
}