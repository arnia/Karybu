<?php
    /**
     * @class  shopAdminView
     * @author Arnia (xe_dev@arnia.ro)
     *  shop module admin view class
     **/

    class shopAdminView extends shop {

        /**
         * Initialization
         **/
        public function init() {
            $oShopModel = getModel('shop');

            $this->setTemplatePath($this->module_path."/tpl/");
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);
        }

        public function dispShopAdminList() {
            $vars = Context::getRequestVars();
            $oShopModel = getModel('shop');

            $page = Context::get('page');
            if(!$page) $page = 1;
            $args = new stdClass();
            if($vars->search_target && $vars->search_keyword) {
                $args->{'s_'.$vars->search_target} = strtolower($vars->search_keyword);
            }

            $args->list_count = 20;
            $args->page = $page;
            $args->list_order = 'regdate';
            $output = $oShopModel->getShopList($args);
            if(!$output->toBool()) return $output;

            Context::set('shop_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);

            $this->setTemplateFile('list');
        }

        public function dispShopAdminInsert() {
            $oModuleModel = getModel('module');
            $oMemberModel = getModel('member');
			
            //set identifier type of admin
        	$memberConfig = $oMemberModel->getMemberConfig();
            foreach($memberConfig->signupForm as $item){
            	if($item->isIdentifier) $identifierName = $item->name;
            }
            Context::set('identifier',$identifierName);
            
            $module_srl = Context::get('module_srl');
            if($module_srl) {
                $oShopModel = getModel('shop');
                $shop = $oShopModel->getShop($module_srl);
                Context::set('shop', $shop);

                $admin_list = $oModuleModel->getSiteAdmin($shop->site_srl);
                $site_admin = array();
                if(is_array($admin_list)){
                    foreach($admin_list as $k => $v){
                    	if($identifierName == 'user_id')  $site_admin[] = $v->user_id;
                    	   else $site_admin[] = $v->email_address;
                    }

                    Context::set('site_admin', join(',',$site_admin));
                }
            }
            
            
            $skin_list = $oModuleModel->getSkins($this->module_path);
            Context::set('skin_list',$skin_list);

            $this->setTemplateFile('insert');
        }

        public function dispShopAdminDelete() {
            if(!Context::get('module_srl')) return $this->dispShopAdminList();
            $module_srl = Context::get('module_srl');

            $oShopModel = getModel('shop');
            $oShop = $oShopModel->getShop($module_srl);
            $shop_info = $oShop->getObjectVars();

            $oDocumentModel = getModel('document');
            $document_count = $oDocumentModel->getDocumentCount($shop_info->module_srl);
            $shop_info->document_count = $document_count;

            Context::set('shop_info',$shop_info);

            $this->setTemplateFile('shop_delete');
        }

    }
?>
