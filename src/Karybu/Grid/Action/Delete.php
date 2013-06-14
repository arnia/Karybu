<?php
namespace Karybu\Grid\Action;
use Karybu\Grid\Action\Action;
class Delete extends Action{
    public function render($row){
        $className = $this->getConfig('class_name');
        $ruleset = $this->getConfig('ruleset');
        $module = $this->getConfig('module');
        $proc = $this->getConfig('act');
        $params = $this->getConfig('params');
        $title = $this->getConfig('title');
        $iconClass = $this->getConfig('icon_class');
        $csrf = new \Karybu\Security\Csrf();
        $formKeyName = $csrf->getFormKeyName();
        $formKey = $csrf->getSessionFormKey();
        $html = '<form class="'.$className.'" action="./" method="post" style="margin-bottom: 0;">';
        if ($ruleset){
            $html .= '<input type="hidden" name="ruleset" value="'.$ruleset.'" />';
        }
        if ($module){
            $html .= '<input type="hidden" name="module" value="'.$module.'" />';
        }
        if ($proc){
            $html .= '<input type="hidden" name="act" value="'.$proc.'" />';
        }
        $html .= '<input type="hidden" value="'.$this->getConfig('mid').'" name="mid" />';
        $html .= '<input type="hidden" value="'.$this->getConfig('vid').'" name="vid" />';
        $html .= '<input type="hidden" value="'.htmlspecialchars(getRequestUriByServerEnviroment()).'" name="error_return_url" />';
        $html .= '<input type="hidden" name="'.$formKeyName.'" value="'.$formKey.'" />';
        if (is_array($params)){
            foreach ($params as $key=>$param){
                $html .= '<input type="hidden" name="'.$key.'" value="'.(isset($row->$param)?$row->$param:'').'" />';
            }
        }
        $html .='<a href="#" data-toggle="tooltip" title="'.$title.'" onclick="jQuery(this).closest(\'form\').submit()"><i class="'.$iconClass.'">'.$title.'</i></a>';
        $html .='</form>';
        return $html;
    }
}

//<form class="layout_delete_form" ruleset="deleteLayout" action="./" method="post" style="margin-bottom: 0;">
//    <input type="hidden" name="module" value="layout" />
//    <input type="hidden" name="act" value="procLayoutAdminDelete" />
//    <input type="hidden" name="layout_srl" value="{$item->layout_srl}" />
//    <a href="#" data-toggle="tooltip" title="{$lang->cmd_delete}" onclick="jQuery(this).closest('form').submit()"><i class="kDelete">{$lang->cmd_delete}</i></a>
//</form>