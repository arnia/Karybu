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

		function procMobile_applicationAdminGenerateStaticPage(){
			try {
				if (file_exists($this->getPhonegapWriteAccessDir() . '/' . 'archive.tar.gz')) {
					unlink($this->getPhonegapWriteAccessDir() . '/' . 'archive.tar.gz');
				}
				$tarPath = $this->getPhonegapWriteAccessDir() . '/' . 'archive.tar';
				$tarData = new PharData($tarPath);
                $this->writeIndexFile($tarData);
                $tarData->addFile($this->getPhonegapWriteAccessDir() . '/' . 'index.html', 'index.html');
                // Add phonegap files
                $tarData->buildFromDirectory(_KARYBU_PATH_ . 'modules/mobile_application/karybu_app/www');
                $tarData->compress(Phar::GZ);
				unlink($tarPath);
				$this->serveTarGz($this->getPhonegapWriteAccessDir() . '/' . 'archive.tar.gz');
			}
			catch (Exception $e) {
				echo "Exception : " . $e;
			}
			die;
		}

		private function writeIndexFile($tarData){
			$content = $this->getFileIndexHtml();

            // add html resource file to phonegap
            $content = $this->getResourceFiles($content, $tarData);

			return file_put_contents($this->getPhonegapWriteAccessDir() . '/' . 'index.html', $content);
		}

		private function serveTarGz($path){
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Length: ' . filesize($path));
			header('Content-Disposition: attachment; filename=' . basename($path));
			readfile($path);
		}

		protected function getPhonegapWriteAccessDir(){
			$writeAccessDir = _KARYBU_PATH_ . 'modules/mobile_application/karybu_app';
			if (!file_exists($writeAccessDir)) {
				if (!mkdir( $writeAccessDir, 0755, true)) {
					throw new Exception( 'Cannot create phonegap dir' );
				}
			}
			return $writeAccessDir;
		}

		private function getFileIndexHtml(){
		    $contextInstance = Context::getInstance();
		    $siteInfo = $contextInstance->getSiteModuleInfo();
		    $layoutModel = getModel('layout');
		    $layoutInfo = $layoutModel->getLayout($siteInfo->layout_srl);

            $menuAdminModel = getAdminModel('menu');
		    $menuInfo = $menuAdminModel->getMenu($layoutInfo->main_menu);
		    $menuItems = $menuAdminModel->getMenuItems($layoutInfo->main_menu);

		    $oTemplate = &TemplateHandler::getInstance();


            // Set menus into context
            if ($layoutInfo->menu_count) {
                foreach ($layoutInfo->menu as $menu_id => $menu) {
                    if (!file_exists($menu->php_file)) {
                        //create menu cache if it doesn't exist
                        $oMenuAdminController = getAdminController('menu');
                        Context::set('menu_srl', $menu->menu_srl);
                        $oMenuAdminController->procMenuAdminMakeXmlFile();
                    }
                    //failsave in case the cache is not created
                    if (file_exists($menu->php_file)){
                        include($menu->php_file);
                    }
                    Context::set($menu_id, $menu);
                }
            }

		    $layoutHtml = $oTemplate->compile($layoutInfo->path,'layout.html');

		    $oModule = getController('widget');
		    $output = $oModule->triggerWidgetCompile($layoutHtml);

		    Context::set('content',$layoutHtml);

            $oContext  =& Context::getInstance();

            // add common JS/CSS files
            $oContext->loadFile(array('./common/js/jquery.min.js', 'head', '', -100000), true);
            $oContext->loadFile(array('./common/js/x.min.js', 'head', '', -100000), true);
            $oContext->loadFile(array('./common/js/xe.min.js', 'head', '', -100000), true);

		    $output = $oTemplate->compile('./common/tpl', 'common_layout');

            //echo $output;die();

			return $output;
		}

        private function getResourceFiles($content, $tarData){
            // replace localhost with host IP address
            $ipAddress = gethostbyname(trim(`hostname`));
            $content = str_replace('localhost', $ipAddress, $content);

            // Get css & js resource files
            $cssFileCount = preg_match_all('/(<link\b.+href=")(?!http)([^"]*)(".*>)/', $content, $cssMatches);
            $jsFileCount = preg_match_all('/src=(["\'])(.*?)\1/', $content, $jsMatches);

            if ($cssFileCount == false && $jsFileCount == false)
                return $content;

            // CSS files
            $cssResourceFilesPath = $cssMatches[2];
            for ($i = 0; $i < count($cssResourceFilesPath); $i++){
                // remove karybu
                $tempPath = $cssResourceFilesPath[$i];
                $cssResourceFilesPath[$i] = ltrim($cssResourceFilesPath[$i], '/karybu/');
                // remove timestamp
                $cssResourceFilesPath[$i] = substr($cssResourceFilesPath[$i], 0, strpos($cssResourceFilesPath[$i], '?'));
                $cssResourceFilesPath[$i] = _KARYBU_PATH_. $cssResourceFilesPath[$i];

                $fileName = basename($cssResourceFilesPath[$i]);
                $fileContent = file_get_contents($cssResourceFilesPath[$i]);

                // add css from @import
                $cssImportFileCount = preg_match_all('/@import (url\()?"(.*?)"(?(1)\))/', $fileContent, $cssImportMatches);
                if ($cssImportFileCount != false){
                    $cssDirName = dirname($cssResourceFilesPath[$i]);
                    for ($j = 0; $j < count($cssImportFiles = $cssImportMatches[2]); $j++){
                        $tarData->addEmptyDir('css');
                        $tarData->addFile($cssDirName.'/'.$cssImportFiles[$j], 'css/'.$cssImportFiles[$j]);
                    }
                }

                if ($cssResourceFilesPath[$i] != _KARYBU_PATH_){
                    $tarData->addEmptyDir('css');
                    $tarData->addFile($cssResourceFilesPath[$i], 'css/'.$fileName);
                }

                // replace link href
                $content = str_replace($tempPath, 'css/'.$fileName, $content);
            }

            // JS files
            $jsResourceFilesPath = $jsMatches[2];
            for ($i = 0; $i < count($jsResourceFilesPath); $i++){

                $tempPath = $jsResourceFilesPath[$i];
                if (strpos($jsResourceFilesPath[$i],'http:') === false) {

                    // remove karybu folder from path
                    $jsResourceFilesPath[$i] = ltrim($jsResourceFilesPath[$i], '/karybu/');
                    // remove timestamp
                    if (strpos($jsResourceFilesPath[$i], '?'))
                        $jsResourceFilesPath[$i] = substr($jsResourceFilesPath[$i], 0, strpos($jsResourceFilesPath[$i], '?'));

                    // compute absolute path
                    $jsResourceFilesPath[$i] = _KARYBU_PATH_ . $jsResourceFilesPath[$i];
                } else {
                    // remove http://
                    $jsResourceFilesPath[$i] = ltrim($jsResourceFilesPath[$i], 'http://');
                    // remove host ip
                    $jsResourceFilesPath[$i] = ltrim($jsResourceFilesPath[$i], $ipAddress);
                    // remove karybu folder from path
                    $jsResourceFilesPath[$i] = ltrim($jsResourceFilesPath[$i], '/karybu/');
                }

                $fileInfo = pathinfo($jsResourceFilesPath[$i]);
                $fileName = $fileInfo['basename'];
                $fileExt = $fileInfo['extension'];

                if ($jsResourceFilesPath[$i] != _KARYBU_PATH_){
                    // add js and img files to resource folder
                    $folder = ($fileExt == 'js') ? 'js' : 'img';
                    // replace script src
                    $content = str_replace($tempPath, $folder.'/'.$fileName, $content);

                    try {
                        $tarData->addEmptyDir($folder);
                        $tarData->addFile($jsResourceFilesPath[$i], $folder . '/' . $fileName);
                    }
                    catch (Exception $e) {
                        continue;
                    }
                }
            }

            return $content;
        }
	}
?>