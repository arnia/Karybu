<?php
    /**
     * @class  textyleAdminView
     * @author Arnia (developers@xpresseinge.com)
     * @brief  textyle 모듈의 admin view class
     **/

    class materialAdminView extends material {

        /**
         * @brief 초기화
         **/
        function init() {
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);
        }
        
        function dispMaterialAdminIndex(){
            $oMaterialModel = &getModel('material');
            $logged_info = Context::get('logged_info');
            $page = Context::get('page');
            $args = new stdClass();
            $args->page = $page;
            $args->list_count=10;
            $args->member_srl = $logged_info->member_srl;
            $output = $oMaterialModel->getMaterialList($args);
            $bookmark_url = $oMaterialModel->getBookmarkUrl($logged_info->member_srl);
            
            Context::set('page',$output->page_navigation->cur_page);
            Context::set('bookmark_url',$bookmark_url);
            Context::set('material_list',$output->data);
            Context::set('page_navigation',$output->page_navigation);
            
            $this->setTemplateFile('index');
        }
        
        function dispMaterialAdminTutorial(){
            $oMaterialModel = &getModel('material');
            $logged_info = Context::get('logged_info');
            $bookmark_url = $oMaterialModel->getBookmarkUrl($logged_info->member_srl);
            
            Context::set('bookmark_url',$bookmark_url);
            Context::set('tutorial',true);
            
            $this->setTemplateFile('index');
        }
        
    }

?>
