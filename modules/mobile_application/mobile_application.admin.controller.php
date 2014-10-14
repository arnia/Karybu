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
		
        function procMobile_applicationAdminImageSave(){
            global $lang;
            $args = Context::getRequestVars();
            $returnUrl = '';
            if(empty($args->directory))
                $args->directory='';
            //if user sent new file, replace current file with new file
            if(!empty($args->new_file)){
                $result = $this->mobileModel->replaceFile($args->directory,$args->file_name,$args->new_file);
                if($result){
                    $returnUrl = getNotEncodedUrl('','module',Context::get('module'),'act','dispMobile_applicationAdminFileImage','directory',$args->directory,'file_name',$args->new_file['name']);
                }
            }else{
                //rename file with given file name
                if(strcmp($args->old_file_name,$args->file_name)!==0){
                    //Rename file
                    $result = $this->mobileModel->renameFile($args->directory,$args->old_file_name,$args->file_name);
                    if($result){
                        $returnUrl = getNotEncodedUrl('','module',Context::get('module'),'act','dispMobile_applicationAdminFileImage','directory',$args->directory,'file_name',$args->file_name);
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
                $result = $builder->registerNewAppUsingFile($args->title,$appZipFile,$args->package,$args->version,$args->description,$args->app_keys);
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
                $result = $builder->updateApp($appID,$args->title,$appZipFile,$args->package,$args->version,$args->description,$args->app_keys);
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
                if($args->platform==PhoneGapBuilder::PLATFORM_ANDROID){
                    if(empty($args->keystore))
                        $args->keystore='';
                    if(empty($args->keystore_pwd))
                        $args->keystore_pwd='';
                    if(empty($args->private_pwd))
                        $args->private_pwd='';
                    $keyStorePath = $this->mobileModel->getUploadedFilePath($args->keystore);
                    $output = $builder->updateAndroidKey($args->key_id,$args->key_title,$args->key_alias,$keyStorePath,$args->keystore_pwd,$args->private_pwd);
                    unlink($keyStorePath);
                }else if($args->platform==PhoneGapBuilder::PLATFORM_IOS){
                    $certPath = $this->mobileModel->getUploadedFilePath($args->cert_p12);
                    $provisionPath = $this->mobileModel->getUploadedFilePath($args->provision);
                    $output = $builder->updateIOSKey($args->key_id,$args->title_key,$certPath,$provisionPath,$args->cert_pwd);
                    unlink($certPath);
                    unlink($provisionPath);
                }
                if($output->id){
                    $message = $lang->keystore_update_successfully;
                }else{
                    $message =$lang->keystore_failed_to_update;
                }
            }else{
                //add key
                if($args->platform==PhoneGapBuilder::PLATFORM_ANDROID){
                    $keyStorePath = $this->mobileModel->getUploadedFilePath($args->keystore);
                    $output = $builder->addAndroidKey($keyStorePath,$args->key_title,$args->key_alias,$args->keystore_pwd,$args->private_pwd);
                    unlink($keyStorePath);
                }else if($args->platform==PhoneGapBuilder::PLATFORM_IOS){
                    $certPath = $this->mobileModel->getUploadedFilePath($args->cert_p12);
                    $provisionPath = $this->mobileModel->getUploadedFilePath($args->provision);
                    $output = $builder->addIOSKey($certPath,$provisionPath,$args->title_key,$args->cert_pwd);
                    unlink($certPath);
                    unlink($provisionPath);
                }
                if($output->id){
                    $message = $lang->keystore_registered_successfully;
                }else{
                    $message =$lang->keystore_failed_to_register;
                }
            }
            $this->setMessage($message);
            $this->setRedirectUrl(Context::get('success_return_url'));
        }
        public function procMobile_applicationAdminUnlockKey(){
            global $lang;
            $args= Context::getRequestVars();
            $builder = $this->mobileModel->getPhoneGapBuilder();
            if(empty($args->key_id)){
                //Error key not provided
                $this->setMessage($lang->key_is_not_provided);
                $this->setRedirectUrl(Context::get('error_return_url'));
                return;
            }else{
                //Unlock key
                if($args->platform==PhoneGapBuilder::PLATFORM_ANDROID){
                    $output = $builder->unlockAndroidKey($args->key_id,$args->keystore_pwd,$args->private_pwd);
                }else if($args->platform==PhoneGapBuilder::PLATFORM_IOS){
                    $output = $builder->unlockIOSKey($args->key_id,$args->cert_pwd);
                }
                if($output->id){
                    $message = $lang->keystore_unlocked_successfully;
                }else{
                    $message =$lang->keystore_failed_to_unlock;
                }
            }
            $this->setMessage($message);
            $this->setRedirectUrl(Context::get('success_return_url'));
        }
        public function procMobile_applicationAdminAjaxUnlockKey(){
            global $lang;
            $args= Context::getRequestVars();
            $builder = $this->mobileModel->getPhoneGapBuilder();
            if(empty($args->key_id)){
                //Error key not provided
                return new Object(-1,'invalid key');
            }else{
                //Unlock key
                if($args->platform==PhoneGapBuilder::PLATFORM_ANDROID){
                    $output = $builder->unlockAndroidKey($args->key_id,$args->keystore_pwd,$args->private_pwd);
                }else if($args->platform==PhoneGapBuilder::PLATFORM_IOS){
                    $output = $builder->unlockIOSKey($args->key_id,$args->cert_pwd);
                }
                if($output->id){
                    return ;
                }else{
                    return new Object(-1,$lang->keystore_failed_to_unlock);
                }
            }
        }
        function procMobile_applicationAdminRename(){
            global $lang;
            $args = Context::getRequestVars();
            if(empty($args->directory))
                $args->directory='';
            if($this->mobileModel->renameFile($args->directory,$args->old_name,$args->new_name)){
                $message=$lang->rename_successfully;
            }else{
                $message=$lang->rename_failed;
            }
            $this->setMessage($message);
            $this->setRedirectUrl(Context::get('success_return_url'));
        }
        function procMobile_applicationAdminDeleteFolder(){
            global $lang;
            $args = Context::getRequestVars();
            $this->mobileModel->deleteFolder($args->directory._DS_.$args->name);
            $this->setMessage($lang->delete_folder_successfully);
            $this->setRedirectUrl(Context::get('success_return_url'));
        }
        function procMobile_applicationAdminDeleteFile(){
            global $lang;
            $args = Context::getRequestVars();
            $this->mobileModel->deleteFile($args->directory,$args->name);
            $this->setMessage($lang->delete_file_successfully);
            $this->setRedirectUrl(Context::get('success_return_url'));
        }
        function procMobile_applicationAdminCreateFolder(){
            global $lang;
            $args = Context::getRequestVars();
            if(empty($args->directory))
                $args->directory='';
            $this->mobileModel->createFolder($args->directory,$args->new_folder);
            $this->setMessage($lang->create_folder_successfully);
            $this->setRedirectUrl(Context::get('success_return_url'));
        }
        function procMobile_applicationAdminCreateFile(){
            global $lang;
            $args = Context::getRequestVars();
            if(empty($args->directory))
                $args->directory='';
            $this->mobileModel->createFile($args->directory,$args->new_file_name);
            $this->setMessage($lang->create_folder_successfully);
            $this->setRedirectUrl(Context::get('success_return_url'));
        }
        function procMobile_applicationAdminUploadFile(){
            global $lang;
            $args = Context::getRequestVars();
            if(empty($args->directory))
                $args->directory='';
            if($this->mobileModel->uploadFile($args->directory,$args->new_file)){
                $this->setMessage($lang->uploaded_file_successfully);
            }else{
                $this->setMessage($lang->uploaded_file_failed);
            }
            $this->setRedirectUrl(Context::get('success_return_url'));
        }

	}
?>