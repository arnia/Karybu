<?php

use Karybu\Exception\ModuleDoesNotExistException;
use Karybu\Exception\InvalidRequestException;

require_once ('ModuleKey.php');

class ModuleMatcher
{
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
        if ($module == "install") {
            if (!$act || !isset($xml_info->action->{$act})) {
                $act = $xml_info->default_index_act;
            }
        }

        // if act exists, find type of the action, if not use default index act
        if (!$act) {
            $act = $xml_info->default_index_act;
        }

        // still no act means error
        if (!$act) {
            throw new ModuleDoesNotExistException();
        }

        return $act;
    }

    public function getKind($act, $module)
    {
        $kind = strpos(strtolower($act), 'admin') !== false ? 'admin' : '';
        if (!$kind && $module == 'admin') {
            $kind = 'admin';
        }
        return $kind;
    }

    public function getType($act, $xml_info, $is_mobile, $is_installed)
    {
        if(isset($xml_info->action->{$act}->type)) $type = $xml_info->action->{$act}->type;
        if(isset($type)){
            if ($type == 'view' && $is_mobile && $is_installed) {
                $type = 'mobile';
            }
            return $type;
        }
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
        $type = $module_matcher->getType(
            $action_name
            ,
            $xml_info
            ,
            $is_mobile
            ,
            $is_installed
        );

        // 3. Get act 'kind' - admin or not
        $kind = $module_matcher->getKind($action_name, $request_module);

        return new ModuleKey($request_module, $type, $kind);
    }

    public function getModuleInstance(
        $request_act,
        $request_module,
        $oModuleModel,
        $is_mobile,
        $is_installed,
        $module_info
    ) {
        // Get action information with conf/module.xml
        $xml_info = $oModuleModel->getModuleActionXml($request_module);

        $module_matcher = new ModuleMatcher();
        $module_key = $module_matcher->getModuleKey(
            $request_act,
            $request_module,
            $xml_info,
            $is_mobile,
            $is_installed
        );
        $act = $module_matcher->getActionName($request_act, $request_module, $xml_info);

        // Get the instance
        $oModule = ModuleHandler::getModuleInstanceFromKeyAndAct($module_key, $act);

        // If the module still wasn't found, we return
        if (!is_object($oModule)) {
            throw new ModuleDoesNotExistException();
        }

        // If there is no such action in the module object
        // Try to find another key, based on action name instead of request params
        // and if that still doesn't work, based on action forward
        // and if that still doesn't work, just get default action for current module
        if (!isset($xml_info->action->{$act}) || !method_exists($oModule, $act)) {
            if (!Context::isInstalled()) {
                throw new InvalidRequestException();
            }

            $forward = null;
            // 1. Look for the module specified in the action name (dispPageIndex -> page)
            if (preg_match('/^([a-z]+)([A-Z])([a-z0-9\_]+)(.*)$/', $act, $matches)) {
                $forward = new stdClass();
                $module = strtolower($matches[2] . $matches[3]);
                $xml_info = $oModuleModel->getModuleActionXml($module);
                if ($xml_info->action->{$act}) {
                    $forward->module = $module;
                    $forward->type = $xml_info->action->{$act}->type;
                    $forward->ruleset = $xml_info->action->{$act}->ruleset;
                    $forward->act = $act;
                }
            }

            // 2. If it still wasn't found, look for the module in the database
            if (!$forward) {
                $forward = $oModuleModel->getActionForward($act);
            }

            // 3.1. If forward was found ..
            if ($forward->module && $forward->type && $forward->act && $forward->act == $act) {
                $xml_info = $oModuleModel->getModuleActionXml($forward->module);
                $module_key = $module_matcher->getModuleKey(
                    $forward->act,
                    $forward->module,
                    $xml_info,
                    $is_mobile,
                    $is_installed
                );

                $oModule = ModuleHandler::getModuleInstanceFromKeyAndAct($module_key, $forward->act);

                if (!is_object($oModule)) {
                    throw new ModuleDoesNotExistException();
                }
            } // 3.2. Otherwise, fallback to module's default action
            else {
                if ($xml_info->default_index_act && method_exists($oModule, $xml_info->default_index_act)) {
                    $act = $xml_info->default_index_act;
                } // 3.3 Else, error
                else {
                    throw new InvalidRequestException();
                }
            }
        }

        $oModule->module_key = $module_key;
        if (isset($forward->ruleset)) {
            $oModule->ruleset = $forward->ruleset;
        } else {
            $oModule->ruleset = $xml_info->action->{$act}->ruleset;
        }

        $oModule->setAct($act);

        $module_info->module_type = $module_key->getType();
        $oModule->setModuleInfo($module_info, $xml_info);

        if (isset($forward)) {
            if ($forward->module && $forward->type && $forward->act && $forward->act == $act) {
                $oModule->checkAdminPermission = true;
            }
        }

        return $oModule;
    }

}
