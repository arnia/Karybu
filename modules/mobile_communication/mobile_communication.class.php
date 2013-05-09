<?php
    /**
     * @class  mobile_communication
     * @author  ()
     * @brief  base class for mobile_communication module 
     **/
    class mobile_communication extends ModuleObject {

        /**
         * @brief Actions to be performed on module installation
         **/
        function moduleInstall() {
            return new Object();
        }

        /**
         * @brief Checks if the module needs to be updated
         **/
        function checkUpdate() {
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
