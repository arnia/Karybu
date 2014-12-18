<?php
class mobile_communicationView extends mobile_communication {
    function init(){
        $this->setTemplatePath($this->module_path.'/skins/');
    }
    /**
     * Mobile communication for client APIs
     * */
    function dispmobile_communicationGetMenu(){
        $moduleModel = getModel('module');
        $defaultModule = $moduleModel->getDefaultMid();
        $layoutModel = getModel('layout');
        $layoutInfo = $layoutModel->getLayout($defaultModule->layout_srl);
        //Get menu from cache to variable $menu
        @include $layoutInfo->menu->main_menu->php_file;
        $this->_reply($menu);
    }

    /**
     * Get the index page content of the site
     *@return string html content
     */
    function dispmobile_communicationGetIndexPage(){
        $moduleModel = getModel('module');
        $defaultModule = $moduleModel->getDefaultMid();
        $this->_reply($this->_getModuleContent($defaultModule));
    }

    /**
     * Get page content by mid
     * @param module_id refers to mid
     * @return string html content
     */
    function dispmobile_communicationGetPageContentByMid(){
        $mid= Context::get('module_id');
        if(empty($mid)){
            $this->_reply(null,-1,'please set "module_id" parameter.');
        }
        $oModule = getModel('module');
        $moduleInfo = $oModule->getModuleInfoByMid($mid);
        $this->_reply($this->_getModuleContent($moduleInfo));
    }

    private function _getModuleContent($moduleInfo){
        $content='';
        switch ($moduleInfo->page_type){
            case 'ARTICLE':
                //Get content of the document
                $oDocumentModel = getModel('document');
                $oDocument = $oDocumentModel->getDocument($moduleInfo->document_srl, true);
                $content = $oDocument->variables['content'];
                break;
            case 'WIDGET':
                //Call to widget template compiler
                $oWidgetController = & getController('widget');
                $content = $oWidgetController->transWidgetCode($moduleInfo->content);
                break;
        }
        return $content;
    }
    private function _reply($data,$error=0,$msg='success'){
        $obj = new Object();
        $obj->error=$error;
        $obj->setMessage($msg);
        $obj->add('data',$data);
        echo json_encode($obj);
        exit();
    }
    function dispmobile_communicationTestPage(){
        $functions = array(
            'dispmobile_communicationGetMenu'=>array(),
            'dispmobile_communicationGetIndexPage'=>array(),
            'dispmobile_communicationGetPageContentByMid'=>array('module_id'=>'text')
        );
        Context::set('functions',$functions);
        $this->setTemplateFile('test');
    }
}