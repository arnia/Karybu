<?php
/**
 * @class  mobile_applicationView
 * @author Arnia (contact@arnia.ro)
 * @brief  View class of the mobile_application module
 **/
class mobile_applicationView extends mobile_application {
    /**
     * @brief Initialization
     **/
    function init() {
		/**
         * set the template path for module
         **/
        $template_path = sprintf("%sskins/default/",$this->module_path);
        $this->setTemplatePath($template_path);
    }

	/**
     * @brief display mobile_application content
     **/
    function dispMobile_applicationContent() {
        /**
         * get and set module message to be displayed
         **/
        $module_message = $this->module_info->module_message;
        Context::set('module_message', $module_message);
        /**
         * get and set module_srl to be displayed
         **/
        $module_srl = $this->module_info->module_srl;
        Context::set('module_srl', $module_srl);

        /**
         * set template file
         **/
        $this->setTemplateFile('mobile_application_index');
    }

    function dispMobile_applicationEmailList(){
        /**
         * set module_srl for the query arguments
         **/
        $args->module_srl = $this->module_info->module_srl;
        /**
         * execute query
         **/
        $output = executeQueryArray("mobile_application.getEmails",$args);
        /**
         * set template and email_list
         **/
        Context::set('email_list',$output->data);
        $this->setTemplateFile('mobile_application_email_list');
    }
}
