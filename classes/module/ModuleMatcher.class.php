<?php

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
    private $original_module;
    private $original_act;
    private $original_mid;
    private $original_document_srl;
    private $original_module_srl;
    private $original_entry;

    private $module;
    private $act;
    private $mid;
    private $document_srl;
    private $module_srl;
    private $entry;

    public function __construct($module, $act, $mid, $document_srl, $module_srl, $entry)
    {
        $this->original_module = $module;
        $this->original_act = $act;
        $this->original_mid = $mid;
        $this->original_document_srl = $document_srl;
        $this->original_module_srl = $module_srl;
        $this->original_entry = $entry;

        $this->module = $module;
        $this->act = $act;
        $this->mid = $mid;
        $this->document_srl = $document_srl;
        $this->module_srl = $module_srl;
        $this->entry = $entry;
    }

    public function match(moduleModel $oModuleModel, $site_module_info, documentModel $oDocumentModel, $default_url /*db_info defalut url*/)
    {
        $module_info = null;

        if(!$this->document_srl && $this->mid && $this->entry) {
            $this->document_srl = $oDocumentModel->getDocumentSrlByAlias($this->mid, $this->entry);
        }


        // Get module's information based on document_srl, if it's specified
        if($this->document_srl && !$this->module) {
            $module_info = $oModuleModel->getModuleInfoByDocumentSrl($this->document_srl);

            // If the document does not exist, remove document_srl
            if(!$module_info) {
                unset($this->document_srl);
            } else {
                // If it exists, compare mid based on the module information
                // if mids are not matching, set it as the document's mid
                if($this->mid != $module_info->mid) {
                    $mid_mismatch_exception = new MidMismatchException();
                    $mid_mismatch_exception->setRedirectInfo($module_info->mid, $this->document_srl);
                    throw $mid_mismatch_exception;
                }
            }
            // if requested module is different from one of the document, remove the module information retrieved based on the document number
            if($this->module && $module_info->module != $this->module) unset($module_info);
        }

        // If module_info is not set yet, and there exists mid information, get module information based on the mid
        if(!$module_info && $this->mid) {
            $module_info = $oModuleModel->getModuleInfoByMid($this->mid, $site_module_info->site_srl);
            //if($this->module && $module_info->module != $this->module) unset($module_info);
        }

        // redirect, if module_site_srl and site_srl are different
        // If the site_srl of the default module set for the main website is other than 0, redirect to subdomain url
        if(!$this->module && !$module_info && $site_module_info->site_srl == 0 && $site_module_info->module_site_srl > 0) {
            // Retrieve info of the site associated with the default module
            $site_info = $oModuleModel->getSiteInfo($site_module_info->module_site_srl);
            $site_srl_mismatch_exception = new DefaultModuleSiteSrlMismatchException();
            $site_srl_mismatch_exception->setRedirectInfo($site_info->domain, $site_module_info->mid);
            throw $site_srl_mismatch_exception;
        }

        // If module_info is not set still, and $module does not exist, find the default module
        if(!$module_info && !$this->module && !$this->mid) $module_info = $site_module_info;

        if(!$module_info && !$this->module && $site_module_info->module_site_srl) $module_info = $site_module_info;

        // redirect, if site_srl of module_info is different from one of site's module_info
        if($module_info && $module_info->site_srl != $site_module_info->site_srl && !isCrawler()) {
            // If the module is of virtual site
            if($module_info->site_srl) {
                $site_info = $oModuleModel->getSiteInfo($module_info->site_srl);
                $site_srl_mismatch_exception = new SiteSrlMismatchException();
                $site_srl_mismatch_exception->setRedirectInfo(
                    $site_info->domain
                    , $this->original_mid
                    , $this->original_document_srl
                    , $this->original_module_srl
                    , $this->original_entry
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
                        , $this->original_mid
                        , $this->original_document_srl
                        , $this->original_module_srl
                        , $this->original_entry
                    );
                    throw $site_srl_mismatch_exception;
                }
            }

        }

        // If module info was set, retrieve variables from the module information
        if($module_info) {
            $this->module = $module_info->module;
            $this->mid = $module_info->mid;
            $this->module_info = $module_info;
        }

        // Set module and mid into module_info
        $this->module_info->module = $this->module;
        $this->module_info->mid = $this->mid;

        // Set site_srl add 2011 08 09
        $this->module_info->site_srl = $site_module_info->site_srl;

        // Still no module? it's an error
        if(!$this->module)
        {
            throw new ModuleDoesNotExistException();
        }

        $match = new stdClass();
        $match->module = $this->module;
        $match->act = $this->act;
        $match->mid = $this->mid;
        $match->document_srl = $this->document_srl;
        $match->module_srl = $this->module_srl;
        $match->entry = $this->entry;

        $match->module_info = $this->module_info;

        return $match;
    }

}
