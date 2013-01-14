<?php

require_once ('ModuleKey.php');

class MidMismatchException extends Exception
{
    private $mid;
    private $document_srl;

    public function setRedirectInfo($mid, $document_srl)
    {
        $this->mid = $mid;
        $this->document_srl = $document_srl;
    }

    public function getMid()
    {
        return $this->mid;
    }

    public function getDocumentSrl()
    {
        return $this->document_srl;
    }
}

class DefaultModuleSiteSrlMismatchException extends Exception
{
    private $domain;
    private $mid;

    public function setRedirectInfo($domain, $mid)
    {
        $this->domain = $domain;
        $this->mid = $mid;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getMid()
    {
        return $this->mid;
    }
}

class SiteSrlMismatchException extends Exception
{
    private $domain;
    private $mid;
    private $document_srl;
    private $module_srl;
    private $entry;

    public function setRedirectInfo($domain, $mid, $document_srl, $module_srl, $entry)
    {
        $this->domain = $domain;
        $this->mid = $mid;
        $this->document_srl = $document_srl;
        $this->module_srl = $module_srl;
        $this->entry = $entry;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getMid()
    {
        return $this->mid;
    }

    public function getDocumentSrl()
    {
        return $this->document_srl;
    }

    public function getModuleSrl()
    {
        return $this->module_srl;
    }

    public function getEntry()
    {
        return $this->entry;
    }
}

class DefaultUrlNotDefined extends Exception
{

}

class ModuleDoesNotExistException extends Exception
{

}

class ModuleMatcher
{
    public function getModuleInfo($original_module, $original_act, $original_mid, $original_document_srl, $original_module_srl, $original_entry, moduleModel $oModuleModel, $site_module_info, documentModel $oDocumentModel, $default_url /*db_info defalut url*/)
    {
        $module = $original_module;
        $act = $original_act;
        $mid = $original_mid;
        $document_srl = $original_document_srl;
        $module_srl = $original_module_srl;
        $entry = $original_entry;

        $module_info = null;

            if(!$document_srl && $mid && $entry) {
            $document_srl = $oDocumentModel->getDocumentSrlByAlias($mid, $entry);
        }


        // Get module's information based on document_srl, if it's specified
        if($document_srl && !$module) {
            $module_info = $oModuleModel->getModuleInfoByDocumentSrl($document_srl);

            // If the document does not exist, remove document_srl
            if(!$module_info) {
                unset($document_srl);
            } else {
                // If it exists, compare mid based on the module information
                // if mids are not matching, set it as the document's mid
                if($mid != $module_info->mid) {
                    $mid_mismatch_exception = new MidMismatchException();
                    $mid_mismatch_exception->setRedirectInfo($module_info->mid, $document_srl);
                    throw $mid_mismatch_exception;
                }
            }
            // if requested module is different from one of the document, remove the module information retrieved based on the document number
            if($module && $module_info->module != $module) unset($module_info);
        }

        // If module_info is not set yet, and there exists mid information, get module information based on the mid
        if(!$module_info && $mid) {
            $module_info = $oModuleModel->getModuleInfoByMid($mid, $site_module_info->site_srl);
            //if($module && $module_info->module != $module) unset($module_info);
        }

        // redirect, if module_site_srl and site_srl are different
        // If the site_srl of the default module set for the main website is other than 0, redirect to subdomain url
        if(!$module && !$module_info && $site_module_info->site_srl == 0 && $site_module_info->module_site_srl > 0) {
            // Retrieve info of the site associated with the default module
            $site_info = $oModuleModel->getSiteInfo($site_module_info->module_site_srl);
            $site_srl_mismatch_exception = new DefaultModuleSiteSrlMismatchException();
            $site_srl_mismatch_exception->setRedirectInfo($site_info->domain, $site_module_info->mid);
            throw $site_srl_mismatch_exception;
        }

        // If module_info is not set still, and $module does not exist, find the default module
        if(!$module_info && !$module && !$mid) $module_info = $site_module_info;

        if(!$module_info && !$module && $site_module_info->module_site_srl) $module_info = $site_module_info;

        // redirect, if site_srl of module_info is different from one of site's module_info
        if($module_info && $module_info->site_srl != $site_module_info->site_srl && !isCrawler()) {
            // If the module is of virtual site
            if($module_info->site_srl) {
                $site_info = $oModuleModel->getSiteInfo($module_info->site_srl);
                $site_srl_mismatch_exception = new SiteSrlMismatchException();
                $site_srl_mismatch_exception->setRedirectInfo(
                    $site_info->domain
                    , $original_mid
                    , $original_document_srl
                    , $original_module_srl
                    , $original_entry
                );
                throw $site_srl_mismatch_exception;
                // If it's called from a virtual site, though it's not a module of the virtual site
            } else {
                if(!$default_url) {
                    throw new DefaultUrlNotDefined;
                }
                else {
                    $site_srl_mismatch_exception = new SiteSrlMismatchException();
                    $site_srl_mismatch_exception->setRedirectInfo($default_url
                        , $original_mid
                        , $original_document_srl
                        , $original_module_srl
                        , $original_entry
                    );
                    throw $site_srl_mismatch_exception;
                }
            }

        }

        // If module info was set, retrieve variables from the module information
        if($module_info) {
            $module = $module_info->module;
            $mid = $module_info->mid;
            $module_info = $module_info;
        }

        // Set module and mid into module_info
        $module_info->module = $module;
        $module_info->mid = $mid;

        // Set site_srl add 2011 08 09
        $module_info->site_srl = $site_module_info->site_srl;

        // Still no module? it's an error
        if(!$module)
        {
            throw new ModuleDoesNotExistException();
        }

        $match = new stdClass();
        $match->module = $module;
        $match->act = $act;
        $match->mid = $mid;
        $match->document_srl = $document_srl;
        $match->module_srl = $module_srl;
        $match->entry = $entry;

        $match->module_info = $module_info;

        return $match;
    }

    /**
     * This represents the $act parameter in the HTTP request
     * or the default_index_action for the current module, of $act is not specified
     *
     * @param $act
     * @param $module
     * @param $xml_info
     * @return mixed
     * @throws ModuleDoesNotExistException
     */
    public function getActionName($act, $module, $xml_info)
    {
        // If not installed yet, modify act
        if($module=="install") {
            if(!$act || !isset($xml_info->action->{$act})) $act = $xml_info->default_index_act;
        }

        // if act exists, find type of the action, if not use default index act
        if(!$act) $act = $xml_info->default_index_act;

        // still no act means error
        if(!$act) {
            throw new ModuleDoesNotExistException();
        }

        return $act;
    }

    public function getKind($act, $module)
    {
        $kind = strpos(strtolower($act),'admin')!==false?'admin':'';
        if(!$kind && $module == 'admin') $kind = 'admin';
        return $kind;
    }

    public function getType($act, $xml_info, $is_mobile, $is_installed)
    {
        $type = $xml_info->action->{$act}->type;
        if($type == 'view' && $is_mobile && $is_installed)
        {
            $type = 'mobile';
        }
        return $type;
    }

    public function getModuleKey($request_act, $request_module, $xml_info, $is_mobile, $is_installed)
    {
        $module_matcher = new ModuleMatcher();

        // 1. Get 'act' retrieved from request
        $action_name = $module_matcher->getActionName($request_act, $request_module, $xml_info);

        // 2. Get 'type' (view, controller ..)
        $type = $module_matcher->getType($action_name
            , $xml_info
            , $is_mobile
            , $is_installed);

        // 3. Get act 'kind' - admin or not
        $kind = $module_matcher->getKind($action_name, $request_module);

        return new ModuleKey($request_module, $type, $kind);
    }

}
