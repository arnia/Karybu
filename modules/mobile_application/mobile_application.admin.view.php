<?php
    /**
     * @class  mobile_applicationAdminView
     * @author Arnia (contact@arnia.ro)
     * @brief  mobile_application admin view of the module class
     **/
//    defined(_KARYBU_MOBILE_APP_DIR_) or die('error');
    class mobile_applicationAdminView extends mobile_application {
        var $menus;
        /**
         * @var mobile_applicationAdminModel $mobileModel
         */
        var $mobileModel;

        /**
         * @var moduleModel
         */
        var $moduleModel;
        /**
         * @brief Initialization
         **/
        function init() {
            $this->moduleModel = getModel('module');
            $this->mobileModel =getAdminModel('mobile_application');
			// Set template path for admin view pages
            $this->setTemplatePath($this->module_path.'tpl');
            // set admin menu
            $this->menus = $this->mobileModel->getAdminMenu();
            Context::set('mobile_app_menus',$this->menus);
            //
		}

        function dispMobile_applicationAdminDirectory() {
            global $lang;
            //Get files and directory of project path
            $directory=Context::get('directory');
            if(empty($directory)) $directory='';
            if($this->mobileModel->isDirectory($directory)){
                Context::set('files',$this->mobileModel->getDirectoryContent($directory));
            }else{
                $this->setMessage($lang->invalid_directory,'error');
            }
            $this->setTemplateFile('file_list');
        }
        function dispMobile_applicationAdminConfig(){
            $moduleConfig = $this->moduleModel->getModuleConfig($this->module);
            Context::set('module_config',$moduleConfig);
            $supportedKeys = $this->mobileModel->getSupportedPlatformKeys();
            Context::set('supportedKeys',$supportedKeys);
            $keys = $this->mobileModel->getAllKeys();
            Context::set('keys',$keys);
            $this->setTemplateFile('configuration');
        }
        function dispMobile_applicationAdminTest(){
            //get app from phonegap
            $apps = $this->mobileModel->getAllPhonegapApps();
            Context::set('apps',$apps);
            $this->setTemplateFile('build_test');
        }
        function dispMobile_applicationAdminUploadToStore(){

        }

        function dispMobile_applicationAdminRegisterApp(){
            $args = Context::getRequestVars();
            $appInfo = $this->mobileModel->getAppInfo($args->app_id);
            Context::set('appInfo',$appInfo);
            //Get available keys
            $keys = $this->mobileModel->getAllKeys();
            Context::set('keys',$keys);
            $this->setTemplateFile('register_app');
        }

        function dispMobile_applicationAdminFileText(){
            //Get File info
            //Get editor from setting
            $editor = $this->getEditor();
            Context::set('editor',$editor);
            $args = Context::getRequestVars();
            $fileObj = $this->mobileModel->getFileContent($args->directory,$args->file_name);
            Context::set('file',$fileObj);
            $this->setTemplateFile('text_editor');
        }
        function dispMobile_applicationAdminFileImage(){
            $args = Context::getRequestVars();
            $imgObj = $this->mobileModel->getImageFile($args->directory,$args->file_name);
            Context::set('img',$imgObj);
            $this->setTemplateFile('image_editor');
        }
        private function getEditor($contentKeyName = 'content', $enableDefaultComponent = true, $height=300) {
            $editorModel = getModel('editor');
            $editConfig = new stdClass();
            $editConfig->primary_key_name = $contentKeyName . '_primary';
            $editConfig->content_key_name = $contentKeyName;
//            $editConfig->allow_fileupload = $enableFileUpload;
            $editConfig->enable_default_component = $enableDefaultComponent;
            $editConfig->enable_component = TRUE;
            $editConfig->resizable = FALSE;
            $editConfig->height = $height;
            $editor = $editorModel->getEditor(0, $editConfig);
            return $editor;
        }
        public function dispMobile_applicationAdminKeyConfig(){
            $args = Context::getRequestVars();
            if(!empty($args->key_id)){
                $keyInfo = $this->mobileModel->getKeyInfo($args->key_id,$args->platform);
                Context::set('key',$keyInfo);
            }
            $this->setTemplateFile('key_configuration');
        }
        public function dispMobile_applicationAdminKeyUnlock(){
            $args = Context::getRequestVars();
            if(!empty($args->key_id)){
                $keyInfo = $this->mobileModel->getKeyInfo($args->key_id,$args->platform);
                Context::set('key',$keyInfo);
            }
            $this->setTemplateFile('unlock_key');
        }
        public function dispMobile_applicationAdminBuildApp(){
            $args = Context::getRequestVars();
            $appInfo = $this->mobileModel->getAppInfo($args->app_id);
            Context::set('appInfo',$appInfo);
            $this->setTemplateFile('rebuild');
        }
        public function dispMobile_applicationAdminTestApp(){
            $appUrl = $this->mobileModel->getTestingUrl();
            $testUrl = "http://emulate.phonegap.com?url={$appUrl}&platform=cordova";
            Context::set('test_url',$testUrl);
            $this->setTemplateFile('test_app');
        }
	}
?>