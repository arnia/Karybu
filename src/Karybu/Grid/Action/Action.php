<?php
namespace Karybu\Grid\Action;
class Action {
    protected $_config = array();
    public function __construct(array $config){
        $this->_config = $config;
    }
    public function getConfig($setting = null){
        if (is_null($setting)){
            return $this->_config;
        }
        if(isset($this->_config[$setting])){
            return $this->_config[$setting];
        }
        return null;
    }
    public function render($row){
        $params = $this->getConfig('url_params');
        $urlParams = array('');
        if ($module = $this->getConfig('module')){
            $urlParams[] = 'module';
            $urlParams[] = $module;
        }
        if ($act = $this->getConfig('act')){
            $urlParams[] = 'act';
            $urlParams[] = $act;
        }
        foreach ($params as $key=>$param) {
            $urlParams[] = $key;
            if (isset($row->$param)){
                $urlParams[] = $row->$param;
            }
            else{
                $urlParams[] = '';
            }
        }
        $url = call_user_func(array('\Context', 'getUrl'), count($urlParams), $urlParams);
        $html = '<a title="'.$this->getConfig('title').'" data-toggle="tooltip" href="'.$url.'"><i class="'.$this->getConfig('icon_class').'">'.$this->getConfig('title').'</i></a>';
        return $html;
    }
}