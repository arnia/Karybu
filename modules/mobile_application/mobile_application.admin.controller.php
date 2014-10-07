<?php
	/**
     * @class  mobile_applicationAdminController
     * @author Arnia (contact@arnia.ro)
     * @brief  mobile_application module of the admin controller class
     **/
	class mobile_applicationAdminController extends mobile_application {

        /**
         * @var mobile_applicationAdminModel
         */
        var $mobileModel;

        /**
         * @var moduleModel
         */
        var $moduleModel;

        /**
         * @var moduleController
         */
        var $moduleController;

        /**
         * @brief Initialization
         **/
        function init() {
            $this->mobileModel=getAdminModel('mobile_application');
            $this->moduleModel = getModel('module');
            $this->moduleController = getController('module');
        }
		
		/**
         * @brief Add a Mobile_application
         **/
       public function procMobile_applicationAdminRun() {
//            $platform = Context::get('platform');
//            $result=array();
//            $output=array();
//            $path = getcwd();
//            $cdCmd = "./modules/mobile_application/karybu_app/";
////            exec($cdCmd,$output,$result);
//           chdir($cdCmd);
//           $path = getcwd();
//            $command = "phonegap run ios \r\n";
//            $result = shell_exec($command);
//            Context::set('result',$result);
//            $this->setRedirectUrl(getUrl('act','dispMobile_applicationAdminContent'));
//           $currentPath = getcwd();
//           chdir(_KARYBU_MOBILE_APP_DIR_);
//           $output= popen('ripple emulate \r\n','r');
//           $content='';
//           while (feof($output)!==false){
//               $buffer = fread($output,1000);
//               $content.=$buffer;
//           }
//           die($content);
           $appBuilder = PhoneGapBuilder::getInstance('tith.darayong@gmail.com','darayong');
//           $result = $appBuilder->registerNewAppUsingFile('test',_KARYBU_MOBILE_APP_DIR_._DS_.'www.zip');
           $apps =$appBuilder->getUserApps();
           print_r($apps);
//           $this->mobileModel->createProjectZip();
           die();
        }

        private function executePhoneGapCommand(){

        }
        function procMobile_applicationAdminImageSave(){
            global $lang;
            $args = Context::getRequestVars();
            $returnUrl = '';
            //if user sent new file, replace current file with new file
            if(!empty($args->new_file)){
                $result = $this->mobileModel->replaceFile($args->directory,$args->file_name,$args->new_file);
                if($result){
                    $returnUrl = getNotEncodedUrl('','module',$this->module,'act','dispMobile_applicationAdminFileImage','directory',$args->directory,'file_name',$args->new_file['name']);
                }
            }else{
                //rename file with given file name
                if(strcmp($args->old_file_name,$args->file_name)!==0){
                    //Rename file
                    $result = $this->mobileModel->renameFile($args->directory,$args->old_file_name,$args->file_name);
                    if($result){
                        $returnUrl = getNotEncodedUrl('','module',$this->module,'act','dispMobile_applicationAdminFileImage','directory',$args->directory,'file_name',$args->file_name);
                    }
                }
            }
            if($result==true){
                $this->setMessage($lang->image_save_successfully);
            }else{
                $returnUrl = Context::get('error_return_url');
                $this->setMessage($lang->image_fail_to_save,'error');
            }
            $this->setRedirectUrl($returnUrl);
        }
        function procMobile_applicationAdminSaveTextFile(){
            global $lang;
            $module=Context::get('module');
            $args= Context::getRequestVars();
            $result = $this->mobileModel->saveFileContent($args->directory,$args->file_name,$args->content);
            if($result!==false){
                $message = $lang->file_save_successfully;
            }else{
                $message = $lang->file_fail_to_save;
            }
            $this->setMessage($message);
            $this->setRedirectUrl(getNotEncodedUrl('','module',$module,'act','dispMobile_applicationAdminFileText','directory',$args->directory,'file_name',$args->file_name));
        }

        function procMobile_applicationAdminSaveConfiguration(){
            global $lang;
            $config = Context::getRequestVars();
            $moduleConfig = $this->moduleModel->getModuleConfig($this->module);
            if(empty($moduleConfig)){
                //insert new module config
                $output = $this->moduleController->insertModuleConfig($this->module,$config);
            }else{
                //update module config
                $output = $this->moduleController->updateModuleConfig($this->module,$config);
            }
            if($output->toBool()){
                $this->setMessage($lang->module_config_save_successfully);
            }else{
                $this->setMessage($lang->module_config_fail_to_save);
            }
            $this->setRedirectUrl(getNotEncodedUrl('','module',Context::get('module'),'act','dispMobile_applicationAdminConfig'));
        }


        function procMobile_applicationAdminSaveApp(){
            global $lang;
            $args = Context::getRequestVars();
            $builder = $this->mobileModel->getPhoneGapBuilder();
            $appID = Context::get('app_id');
            $appZipFile = $this->mobileModel->createProjectZip();
            if(empty($appID)){
                $result = $builder->registerNewAppUsingFile($args->title,$appZipFile,$args->package,$args->version,$args->description);
                //if successfully create save app_id
                if(is_object($result) && !empty($result->id)){
                    $args = new stdClass();
                    $args->app_id = $result->id;
                    $output = executeQuery('mobile_application.insertApp',$args);
                    $message=$lang->register_app_successfully;
                }else{
                    $message = $result->error;
                }
            }else{
                //update app in phonegap
                $result = $builder->updateApp($appID,$args->title,$appZipFile,$args->package,$args->version,$args->description);
                $message=$lang->update_app_successfully;
            }
            $this->setMessage($message);
            $this->setRedirectUrl(getUrl('','module',Context::get('module'),'act','dispMobile_applicationAdminTest'));
        }
        function procMobile_applicationAdminDeleteApp(){
            global $lang;

            $builder = $this->mobileModel->getPhoneGapBuilder();
            $appID = Context::get('app_id');
            $result = $builder->deleteApp($appID);
            if(!empty($result->success)){
                $this->setMessage($lang->delete_app_successfully);
                //delete app from phonegap_app
                $args = new stdClass();
                $args->app_id = $appID;
                $output = executeQuery('mobile_application.deleteApp',$args);
            }else{
                $this->setMessage($lang->delete_app_failed);
            }
            $this->setRedirectUrl(getUrl('','module',Context::get('module'),'act','dispMobile_applicationAdminTest'));
        }

        function procMobile_applicationAdminDeleteKey(){
            global $lang;
            $args=Context::getRequestVars();
            $builder = $this->mobileModel->getPhoneGapBuilder();
            $output = $builder->deleteKey($args->key_id,$args->platform);
            if(!empty($output->success)){
                $msg = $lang->delete_key_successfully;
            }else{
                $msg = $lang->delete_key_failed;
            }
            $this->setMessage($msg);
            $this->setRedirectUrl(Context::get('success_return_url'));
        }
        function procMobile_applicationAdminSaveKey(){
            global $lang;
            $args= Context::getRequestVars();
            $builder = $this->mobileModel->getPhoneGapBuilder();
            if(!empty($args->key_id)){
                //update key

            }else{
                //add key
                if($args->platform==PhoneGapBuilder::PLATFORM_ANDROID){
                    $keyStorePath = $this->mobileModel->getKeyStoreFilePath($args->keystore);
                    $output = $builder->addAndroidKey($keyStorePath,$args->key_title,$args->key_alias,$args->keystore_pwd,$args->private_pwd);
                    unlink($keyStorePath);
                    if($output->id){
                        $message = $lang->keystore_registered_successfully;
                    }else{
                        $message =$lang->keystore_failed_to_register;
                    }
                }else if($args->platform==PhoneGapBuilder::PLATFORM_IOS){
//                    $output = $builder->addIOSKey($args->)
                }
            }
            $this->setMessage($message);
            $this->setRedirectUrl(Context::get('success_return_url'));
        }

	}
?>