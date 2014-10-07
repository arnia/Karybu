<?php
    /**
     * @class  mobile_application
     * @author Arnia (contact@arnia.ro)
     * @brief  base class for mobile_application module 
     **/
    define('_KARYBU_MOBILE_APP_DIR_',_KARYBU_PATH_.'modules/mobile_application/karybu_app/www');
    define('_KARYBU_MOBILE_APP_ZIP_DIR_',_KARYBU_PATH_.'modules/mobile_application/karybu_app');
    define('_KARYBU_MOBILE_APP_KEY_STORE_DIR_',_KARYBU_PATH_.'modules/mobile_application/karybu_app/key');
    define('_DS_',DIRECTORY_SEPARATOR);
    require_once _KARYBU_PATH_.'modules/mobile_application/libs/phonegap_built_api.php';
    require_once _KARYBU_PATH_.'modules/mobile_application/utils/functions.php';
    class mobile_application extends ModuleObject {
        const FILE_TYPE_IMAGE=1;
        const FILE_TYPE_TEXT=2;
        const FILE_TYPE_DIR=3;
        /**
         * @brief Actions to be performed on module installation
         **/
        function moduleInstall() {
//			$oDB = &DB::getInstance();
//
//            $oDB->addIndex("mobile_application_emails","idx_module_mobile_application_emails","module_srl",true);
            return new Object();
        }

        /**
         * @brief Checks if the module needs to be updated
         **/
        function checkUpdate() {
//			$oDB = &DB::getInstance();
//        	if(!$oDB->isColumnExists("mobile_application_emails","email_srl")) return true;
            return false;
        }

        /**
         * @brief Updates module
         **/
        function moduleUpdate() {
            return new Object(0,'success_updated');
        }

        /**
         * @brief Re-generates the cache file
         **/
        function recompileCache() {
        }
    }
?>
