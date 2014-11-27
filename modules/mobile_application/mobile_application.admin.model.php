<?php
    /**
     * @class  mobile_applicationAdminModel
     * @author Arnia (contact@arnia.ro)
     * @brief  mobile_application the module's admin model class
     **/
//    defined(_KARYBU_MOBILE_APP_DIR_)or die('error');

    class mobile_applicationAdminModel extends mobile_application {
        /**
         * @brief Initialization
         **/
        function init() {
        }

        public function getAdminMenu(){
            global $lang;
            $navMenu= array(
                            $lang->menu_mobile_app=>array('dispMobile_applicationAdminDirectory','dispMobile_applicationAdminFileImage','dispMobile_applicationAdminFileText'),
                            $lang->menu_mobile_test=>array('dispMobile_applicationAdminTest','dispMobile_applicationAdminTestApp'),
                            $lang->menu_mobile_upload_to_store=>array('dispMobile_applicationAdminUploadToStore'),
                            $lang->menu_mobile_static_page=>array('dispMobile_applicationAdminStaticPage'),
                            $lang->menu_mobile_config=>array('dispMobile_applicationAdminConfig','dispMobile_applicationAdminKeyConfig'),
                        );
            return $navMenu;
        }

        public function getDirectoryContent($directory=''){
            $fullPath = _KARYBU_MOBILE_APP_DIR_.$directory;
            $ignoreFileTypes=array('.DAV','.DS_Store','.bzr','.bzrignore','.bzrtags','.git','.gitattributes','.gitignore','.gitmodules','.hg','.hgignore','.hgtags','.htaccess','.htpasswd','.jshintrc','.npmignore','.Spotlight-V100','.svn','__MACOSX','ehthumbs.db','robots.txt','Thumbs.db');
            $ignoreDirNames = array('.','..');
            $files = scandir($fullPath);
            $result = array();
            foreach($files as $file){
                if(($file=='.' ||$file=='..')) continue;
                $filePath=_KARYBU_MOBILE_APP_DIR_.$directory._DS_.$file;
                if(is_dir($filePath)){
                    $result[]=$this->getFileObj($directory,$file,self::FILE_TYPE_DIR);
                }else if($this->isImageFile($filePath)){
                    $result[]=$this->getFileObj($directory,$file,self::FILE_TYPE_IMAGE);
                }else if($this->isTextFile($filePath)){
                    $result[]=$this->getFileObj($directory,$file,self::FILE_TYPE_TEXT);
                }
            }
            return $result;
        }

        public function isImageFile($fileName){
            $imageTypes = array('ai','bmp','cdr','emf','eps','gif','icns','ico','jp2','jpe','jpeg','jpg','jpx','pcx','pict','png','psd','psp','svg','tga','tif','tiff','webp','wmf','icon');
            if(array_search($this->getFileExtension($fileName),$imageTypes)!==false){
                return true;
            }
            return false;
        }
        public function isTextFile($fileName){
            $imageTypes = array('html','txt','js','css','xml');
            if(array_search($this->getFileExtension($fileName),$imageTypes)!==false){
                return true;
            }
            return false;
        }

        function getFileExtension($name){
            $parts = explode('.',$name);
            if(count($parts)<=1){
                return '';
            }else{
                return $parts[count($parts)-1];
            }
        }
        private function getFileObj($directory,$name,$type){
            $obj = new stdClass();
            $obj->directory = $directory;
            $obj->name=$name;
            $obj->type=$type;
            return $obj;
        }
        function isDirectory($path){
            return is_dir(_KARYBU_MOBILE_APP_DIR_._DS_.$path);
        }

        function getImageFile($directory,$fileName){
            $fullPath = $this->_getFilePath($directory,$fileName);
            $imageObj = new stdClass();
            $imageObj->src = $this->_getSiteFilePath($directory,$fileName);
            $imageSize = getimagesize($fullPath);
            $imageObj->image_size = new stdClass();
            $imageObj->image_size->width = $imageSize[0];
            $imageObj->image_size->height = $imageSize[1];
            $imageObj->file_name = $fileName;
            $imageObj->directory=$directory;
            return $imageObj;
        }
        function getFileContent($directory,$fileName){
            $fullPath = $this->_getFilePath($directory,$fileName);
            $content = file_get_contents($fullPath);
            $fileObj = new stdClass();
            $fileObj->content = $content;
            $fileObj->file_name = $fileName;
            $fileObj->directory = $directory;
            return $fileObj;
        }
        function renameFile($directory,$oldName,$newName){
            $oldPath = $this->_getFilePath($directory,$oldName);
            $newPath = $this->_getFilePath($directory,$newName);
            return rename($oldPath,$newPath);
        }
        function saveFileContent($directory,$fileName,$content){
            $filePath = $this->_getFilePath($directory,$fileName);
            return file_put_contents($filePath,$content);
        }
        function replaceFile($directory,$fileName,$uploadedFileObj){
            //delete old file
            $result = $this->deleteFile($directory,$fileName);
            if($result){
                $result = $this->uploadFile($directory,$uploadedFileObj);
            }
            return $result;
        }
        function deleteFile($directory,$fileName){
            $filePath = $this->_getFilePath($directory,$fileName);
            return unlink($filePath);
        }
        function uploadFile($directory,$uploadedFileObj){
            $destination = $this->_getFilePath($directory,$uploadedFileObj['name']);
            $result = copy($uploadedFileObj['tmp_name'],$destination);
            if($result){
                $result = chmod($destination,777);
            }
            return $result;
        }

        //-----------Create zip---------------
        public function createProjectZip(){
            $zipArchive = new ZipArchive();
            $zipFile = _KARYBU_MOBILE_APP_ZIP_DIR_._DS_.'karybu.zip';
            $zipArchive->open($zipFile,ZipArchive::CREATE);
            $this->addFolderToArchive($zipArchive,_KARYBU_MOBILE_APP_DIR_,'');
            $zipArchive->close();
            return $zipFile;
        }

        //---------Private functions----------
        private function addFolderToArchive(ZipArchive $zipArchive,$folder,$localFolder){
            $files = scandir($folder);
            $zipArchive->addEmptyDir($localFolder.basename($folder));
            $localFolder.=basename($folder)._DS_;
            foreach($files as $file){
                if($file=='.' || $file=='..') continue;
                $filePath = $folder._DS_.$file;
                if(is_dir($filePath)){
                    $this->addFolderToArchive($zipArchive,$filePath,$localFolder);
                }else{
                    $zipArchive->addFile($filePath,$localFolder.basename($filePath));
                }
            }
        }
        public function getPhoneGapBuilder(){
            $moduleModel = getModel('module');
            $config = $moduleModel->getModuleConfig($this->module);
            $phonegapBuilder = PhoneGapBuilder::getInstance($config->phonegap_username,$config->phonegap_password);
            return $phonegapBuilder;
        }
        private function _getFilePath($directory,$fileName){
            return _KARYBU_MOBILE_APP_DIR_._DS_.(!empty($directory)?$directory._DS_:'').$fileName;
        }

        private function _getSiteFilePath($directory,$fileName){
            return getSiteUrl().'modules'._DS_.$this->module._DS_.'karybu_app'._DS_.'www'._DS_.(!empty($directory)?$directory._DS_:'').$fileName;
        }

        public function getAllPhonegapApps(){
            $builder = $this->getPhoneGapBuilder();
            $apps = $builder->getUserApps();
            $karybuApps = executeQueryArray('mobile_application.getApps');
            $result = array();
            foreach($apps->apps as $app){
                if($this->_appExistsInKarybuApps($app->id,$karybuApps)){
                    $result[]=$app;
                    //get app icon
                    $icon_url = $builder->getAppIcon($app->id);
                    $app->icon->url=$icon_url->location;
                    //get app download url
                    $supportedPlatforms = PhoneGapBuilder::getSupportedPlatforms();
                    foreach($supportedPlatforms as $platform){
                        if($app->status->$platform==PhoneGapBuilder::STATUS_COMPLETE){
                            $fileLocation = $builder->getAppDownload($app->id,$platform);
                            $prop = $platform.'_file';
                            $app->download->$prop = $fileLocation->location;
                        }
                    }
                    $app->test_url = $this->getTestingUrl();
                }
            }
            return $result;
        }

        private function _appExistsInKarybuApps($id,$karybuApps){
            foreach($karybuApps->data as $kApp){
                if($kApp->app_id==$id){
                    return true;
                }
            }
            return false;
        }
        private function _keyExistsInKarybuKeys($id,$karybuKeys){
            foreach($karybuKeys->data as $key){
                if($key->key_id==$id){
                    return true;
                }
            }
            return false;
        }

        public function getAppInfo($id){
            $builder = $this->getPhoneGapBuilder();
            $app = $builder->getAppByID($id);
            return $app;
        }
        public function getTestingUrl(){
            return getSiteUrl().'modules'._DS_.$this->module._DS_.'karybu_app'._DS_.'www';
        }
        public function getSupportedPlatformKeys(){
            return PhoneGapBuilder::getSupportedPlatformKeys();
        }
        public function getSupportedPlatforms(){
            return PhoneGapBuilder::getSupportedPlatforms();
        }
        public function getAllKeys(){
            $builder = $this->getPhoneGapBuilder();
            $keys=$builder->getKeys();
            $appKeys =array();
            $keys = get_object_vars($keys->keys);
            $karybuKeys = executeQueryArray('mobile_application.getAppKeys');
            foreach($keys as $platform => $value){
                foreach($value->all as $key){
                    if($this->_keyExistsInKarybuKeys($key->id,$karybuKeys)) {
                        $appKeys[$platform][] = $key;
                    }
                }
            }
            return $appKeys;
        }
        public function getKeyInfo($id,$platform){
            $builder = $this->getPhoneGapBuilder();
            $keyInfo = $builder->getKeyByID($id,$platform);
            return $keyInfo;
        }
        public function getUploadedFilePath($fileObj){
            $dest = _KARYBU_MOBILE_APP_KEY_STORE_DIR_._DS_.$fileObj['name'];
            $output = move_uploaded_file($fileObj['tmp_name'],$dest);
            if(!$output){
                return '';
            }
            return $dest;
        }
        public function deleteFolder($directory){
            $fullPath = _KARYBU_MOBILE_APP_DIR_.$directory;
            $files = scandir($fullPath);
            foreach($files as $file){
                if(($file=='.' ||$file=='..')) continue;
                $filePath=_KARYBU_MOBILE_APP_DIR_.$directory._DS_.$file;
                if(is_dir($filePath)){
                    $this->deleteFolder($directory._DS_.$file);
                }else{
                    unlink($filePath);
                }
            }
            rmdir($fullPath);
        }
        public function createFolder($directory,$name){
            $fullPath = _KARYBU_MOBILE_APP_DIR_.$directory._DS_.$name;
            return mkdir($fullPath);
        }
        public function createFile($directory,$name){
            $fullPath = _KARYBU_MOBILE_APP_DIR_.$directory._DS_.$name;
            return file_put_contents($fullPath,'');
        }


	}
?>