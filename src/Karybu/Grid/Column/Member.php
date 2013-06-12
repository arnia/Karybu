<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Member extends Column{
    const DEFAULT_MEMBER_KEY_NAME = 'member_srl';
    public function render($row){
        $prefix = '<span class="kActionIcons">';
        $prefix .='<a href="#popup_menu_area" data-toggle="tooltip" class="cMenu member_'.$this->getMemberKey($row).'"  title="'.$this->getConfig('title').'"><i class="kInfo">'.$this->getConfig('title').'</i></a>';
        $prefix .='</span>';
        return $prefix.parent::render($row);
    }
    public function getMemberKey($row){
        $memberKeyName = $this->getConfig('member_key');
        if (!$memberKeyName){
            $memberKeyName = self::DEFAULT_MEMBER_KEY_NAME;
        }
        if (isset($row->$memberKeyName)){
            return $row->$memberKeyName;
        }
        return '';
    }
}