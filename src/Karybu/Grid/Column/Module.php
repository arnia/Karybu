<?php
namespace Karybu\Grid\Column;
use Karybu\Grid;
use Karybu\Grid\Column;

class Module extends Link{
    public function render($row)
    {
        global $lang;
        $html = parent::render($row);

        if (!empty($row->need_install)){
            $html .= '<p class="update well well-small inline">';
            $html .= $lang->msg_avail_install;
            $html .= '<button class="btn btn-primary btn-mini" type="button" onclick="doInstallModule(\''.$row->module.'\')">';
            $html .= $lang->msg_do_you_like_install;
            $html .='<button>';
            $html .= '</p>';
        }

        if (!empty($row->need_update)){
            $html .= '<p class="update well well-small inline">';
            $html .= $lang->msg_avail_update;
            $html .= '<button class="btn btn-primary btn-mini" type="button" onclick="doUpdateModule(\''.$row->module.'\')">';
            $html .= $lang->msg_do_you_like_update;
            $html .='<button>';
            $html .= '</p>';
        }
        if (isset($row->need_autoinstall_update) && $row->need_autoinstall_update == 'Y'){
            if (empty($row->update_url)){
                $row->update_url = '#';
            }
            $html .= '<p class="update well well-small inline">';
            $html .= $lang->msg_avail_easy_update;
            $html .= '<a class="btn btn-primary btn-mini" href="'.$row->update_url.'&amp;return_url='.urlencode(getRequestUriByServerEnviroment()).'">';
            $html .= $lang->msg_do_you_like_update;
            $html .= '</a>';
            $html .= '</p>';
        }
        return $html;
    }
    public function getSortable(){
        return false;
    }
}