<?php
    /**
     * @class  sessionAdminView
     * @author Arnia (dev@karybu.org)
     * @brief The admin view class of the session module
     **/

    class sessionAdminView extends session {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Configure
         **/
        function dispSessionAdminIndex() {
            // Set the template file
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('index');
        }

    }
?>
