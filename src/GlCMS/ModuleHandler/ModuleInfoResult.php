<?php

namespace GlCMS\ModuleHandler;

class ModuleInfoResult {
    private $module_info;
    private $redirect_url;

    public function __construct($module_info, $redirect_url) {
        $this->module_info = $module_info;
        $this->redirect_url = $redirect_url;
    }

    public function isSuccessful() {
        if($this->module_info) return true;
        return false;
    }

    public function getRedirectUrl() {
        return $this->redirect_url;
    }

    public function getModuleInfo() {
        return $this->module_info;
    }
}
