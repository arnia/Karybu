<?php

namespace Karybu\ModuleHandler;

use Karybu\Exception;

/**
 * Returns the properties of a module_instance, given a document_srl, a mid or a mid and an entry
 */
class ModuleInstanceRetriever {

    private $documentModel;
    private $moduleModel;
    private $context;

    public function __construct(\documentModel $documentModel, \moduleModel $moduleModel, \ContextInstance $context) {
        $this->documentModel = $documentModel;
        $this->moduleModel = $moduleModel;
        $this->context = $context;
    }

    public function findModuleInfo($mid, $entry, &$document_srl, $site_module_info) {
        // 1. Search module instance properties based on a document
        $module_info = $this->findModuleInfoByMidAndEntry($mid, $entry, $document_srl);

        if (!$module_info) {
            $module_info = $this->findModuleInfoByDocumentSrl($document_srl);
            if(!$module_info && $document_srl && !$mid)
                throw new Exception\ModuleDoesNotExistException();
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
                throw new Exception\DefaultUrlNotDefinedException();
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
            throw new Exception\ModuleDoesNotExistException();
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