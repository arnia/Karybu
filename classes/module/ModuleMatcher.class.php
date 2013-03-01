<?php

require_once ('ModuleKey.php');

class DefaultUrlNotDefinedException extends Exception
{
    public function __construct() {
        return parent::__construct('msg_default_url_is_not_defined');
    }
}

class ModuleDoesNotExistException extends Exception
{
    public function __construct() {
        return parent::__construct('msg_module_is_not_exists');
    }
}

class InvalidRequestException extends Exception
{

}


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

/**
 * Returns the properties of a module_instance, given a document_srl, a mid or a mid and an entry
 *
 */
class ModuleInstanceRetriever {

    private $documentModel;
    private $moduleModel;
    private $context;

    public function __construct(documentModel $documentModel, moduleModel $moduleModel, ContextInstance $context) {
        $this->documentModel = $documentModel;
        $this->moduleModel = $moduleModel;
        $this->context = $context;
    }

    public function findModuleInfo($mid, $entry, &$document_srl, $site_module_info) {
        // 1. Search module instance properties based on a document
        $module_info = $this->findModuleInfoByMidAndEntry($mid, $entry, $document_srl);

        if (!$module_info) {
            $module_info = $this->findModuleInfoByDocumentSrl($document_srl);
        }

        if(!$module_info) {
            $document_srl = null;
        }
        if ($module_info && $mid != $module_info->mid) {
            return new ModuleInfoResult(null,
                $this->context->getNotEncodedSiteUrl(
                    $site_module_info->domain
                    , 'mid', $module_info->mid
                    , 'document_srl', $document_srl));
        }

        // 2. Search module instance properties based on a mid (module id)
        if (!$module_info && $mid) {
            $module_info = $this->findModuleInfoByMid($mid, $site_module_info->site_srl);
        }

        $current_site_srl = $site_module_info->site_srl;
        $default_module_site_srl = $site_module_info->module_site_srl;
        if(!$module_info && $current_site_srl == 0 && $default_module_site_srl > 0) {
            // Get info of virtual site
            $site_info = $this->moduleModel->getSiteInfo($default_module_site_srl);
            // Redirect to its domain
            return new ModuleInfoResult(null,
                $this->context->getNotEncodedSiteUrl(
                    $site_info->domain
                    , 'mid',$site_module_info->mid)
            );
        }

        // 3. Lastly, just try to use the default module for this request
        // If module_info is not set still, and $module does not exist, find the default module
        if (!$module_info && !$mid) {
            $module_info = $site_module_info;
        }

        // If default module belongs to virtual site
        if (!$module_info && $site_module_info->module_site_srl && $site_module_info->site_srl != 0) {
            $module_info = $site_module_info;
        }

        // redirect, if module_site_srl and site_srl are different
        $current_module_site_srl = $module_info->site_srl;
        // redirect, if site_srl of module_info is different from one of site's module_info
        if($module_info && $current_module_site_srl != $current_site_srl && !$this->context->isCrawler()) {
            // If the module is of virtual site
            if($current_module_site_srl) {
                $site_info = $this->moduleModel->getSiteInfo($current_module_site_srl);
                return new ModuleInfoResult(null,
                    $this->context->getNotEncodedSiteUrl(
                        $site_info->domain
                        , 'mid',$this->context->get('mid')
                        , 'document_srl',$document_srl
                        , 'module_srl',$this->context->get('module_srl')
                        , 'entry',$this->context->get('entry'))
                );
            }

            // If it's called from a virtual site, though it's not a module of the virtual site
            $default_url = $this->context->getDefaultUrl();
            if(!$default_url) {
                throw new DefaultUrlNotDefinedException();
            }

            return new ModuleInfoResult(null,
                $this->context->getNotEncodedSiteUrl(
                    $default_url
                    , 'mid',$this->context->get('mid')
                    , 'document_srl',$document_srl
                    , 'module_srl',$this->context->get('module_srl')
                    , 'entry',$this->context->get('entry'))
            );
        }

        if(!$module_info) {
            throw new ModuleDoesNotExistException();
        }

        return new ModuleInfoResult($module_info, null);
    }


    private function findModuleInfoByDocumentSrl($document_srl) {
        if(!$document_srl) {
            return null;
        }
        return $this->moduleModel->getModuleInfoByDocumentSrl($document_srl);
    }

    private function findModuleInfoByMidAndEntry($mid, $entry, &$document_srl) {
        if (!$mid || !$entry) {
            return null;
        }

        $document_srl = $this->documentModel->getDocumentSrlByAlias($mid, $entry);
        return $this->findModuleInfoByDocumentSrl($document_srl);
    }

    private function findModuleInfoByMid($mid, $site_srl) {
        return $this->moduleModel->getModuleInfoByMid($mid, $site_srl);;
    }
}


class ModuleMatcher
{
    private $documentModel;
    private $moduleModel;

    public function __construct(documentModel $documentModel, moduleModel $moduleModel) {
        $this->documentModel = $documentModel;
        $this->moduleModel = $moduleModel;
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
        if ($module=="install") {
            if(!$act || !isset($xml_info->action->{$act})) $act = $xml_info->default_index_act;
        }

        // if act exists, find type of the action, if not use default index act
        if (!$act) $act = $xml_info->default_index_act;

        // still no act means error
        if (!$act) {
            throw new ModuleDoesNotExistException();
        }

        return $act;
    }

    public function getKind($act, $module)
    {
        $kind = strpos(strtolower($act),'admin')!==false?'admin':'';
        if (!$kind && $module == 'admin') $kind = 'admin';
        return $kind;
    }

    public function getType($act, $xml_info, $is_mobile, $is_installed)
    {
        $type = $xml_info->action->{$act}->type;
        if ($type == 'view' && $is_mobile && $is_installed) {
            $type = 'mobile';
        }
        return $type;
    }

    /**
     * @param $request_act Act retrieved from HTTP request parameters - unchanged by code
     * @param $request_module Module retrieved from ..
     * @param $xml_info
     * @param $is_mobile
     * @param $is_installed
     * @return ModuleKey
     */
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

    public function getModuleInstance($request_act, $request_module, $oModuleModel, $is_mobile, $is_installed, $module_info)
    {
        // Get action information with conf/module.xml
        $xml_info = $oModuleModel->getModuleActionXml($request_module);

        $module_matcher = new ModuleMatcher();
        $module_key = $module_matcher->getModuleKey($request_act, $request_module, $xml_info, $is_mobile,  $is_installed);
        $act = $module_matcher->getActionName($request_act, $request_module, $xml_info);

        // Get the instance
        $oModule = ModuleHandler::getModuleInstanceFromKeyAndAct($module_key, $request_act);

        // If the module still wasn't found, we return
        if(!is_object($oModule)) {
            throw new ModuleDoesNotExistException();
        }

        // If there is no such action in the module object
        // Try to find another key, based on action name instead of request params
        // and if that still doesn't work, based on action forward
        // and if that still doesn't work, just get default action for current module
        if(!isset($xml_info->action->{$act}) || !method_exists($oModule, $act))
        {
            if(!Context::isInstalled())
            {
                throw new InvalidRequestException();
            }

            $forward = null;
            // 1. Look for the module specified in the action name (dispPageIndex -> page)
            if(preg_match('/^([a-z]+)([A-Z])([a-z0-9\_]+)(.*)$/', $act, $matches)) {
                $module = strtolower($matches[2].$matches[3]);
                $xml_info = $oModuleModel->getModuleActionXml($module);
                if($xml_info->action->{$act}) {
                    $forward->module = $module;
                    $forward->type = $xml_info->action->{$act}->type;
                    $forward->ruleset = $xml_info->action->{$act}->ruleset;
                    $forward->act = $act;
                }
            }

            // 2. If it still wasn't found, look for the module in the database
            if(!$forward)
            {
                $forward = $oModuleModel->getActionForward($act);
            }

            // 3.1. If forward was found ..
            if($forward->module && $forward->type && $forward->act && $forward->act == $act) {
                $xml_info = $oModuleModel->getModuleActionXml($forward->module);
                $module_key = $module_matcher->getModuleKey($forward->act, $forward->module, $xml_info, $is_mobile, $is_installed);

                $oModule = ModuleHandler::getModuleInstanceFromKeyAndAct($module_key, $forward->act);

                if(!is_object($oModule)) {
                    throw new ModuleDoesNotExistException();
                }
            }
            // 3.2. Otherwise, fallback to module's default action
            else if($xml_info->default_index_act && method_exists($oModule, $xml_info->default_index_act))
            {
                $act = $xml_info->default_index_act;
            }
            // 3.3 Else, error
            else
            {
                throw new InvalidRequestException();
            }
        }

        $oModule->module_key = $module_key;
        $oModule->ruleset = $forward->ruleset ? $forward->ruleset : $xml_info->action->{$act}->ruleset;

        $oModule->setAct($act);

        $module_info->module_type = $module_key->getType();
        $oModule->setModuleInfo($module_info, $xml_info);

        if($forward->module && $forward->type && $forward->act && $forward->act == $act) {
            $oModule->checkAdminPermission = true;
        }

        return $oModule;
    }

}
