<?php
use Karybu\HttpKernel\Module;

class ModuleObject extends Module
{

    /**
     * old Object class below:
     */

    //region Object
    /**
     * Error code. If `0`, it is not an error.
     * @var int
     */
    var $error = 0;

    /**
     * Error message. If `success`, it is not an error.
     * @var string
     */
    var $message = 'success';

    /**
     * An additional variable
     * @var array
     */
    var $variables = array();

    /**
     * http status code.
     * @var int
     */
    var $httpStatusCode = null;


    /**
     * Constructor
     *
     * @param int $error Error code
     * @param string $message Error message
     * @return void
     */
    public function __construct($error = 0, $message = 'success')
    {
        $this->setError($error);
        $this->setMessage($message);
    }


    /**
     * Setter to set error code
     *
     * @param int $error error code
     * @return void
     */
    function setError($error = 0)
    {
        $this->error = $error;
    }

    /**
     * Getter to retrieve error code
     *
     * @return int Returns an error code
     */
    function getError()
    {
        return $this->error;
    }

    /**
     * Setter to set HTTP status code
     *
     * @param int $code HTTP status code. Default value is `200` that means successful
     * @return void
     */
    function setHttpStatusCode($code = '200')
    {
        $this->httpStatusCode = $code;
    }

    /**
     * Getter to retrieve HTTP status code
     *
     * @return int Returns HTTP status code
     */
    function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Getter to retrieve an error message
     *
     * @return string Returns message
     */
    function getMessage()
    {
        return $this->message;
    }

    /**
     * Setter to set a key/value pair as an additional variable
     *
     * @param string $key A variable name
     * @param mixed $val A value for the variable
     * @return void
     */
    function add($key, $val)
    {
        $this->variables[$key] = $val;
    }

    /**
     * Method to set multiple key/value pairs as an additional variables
     *
     * @param Object|array $object Either object or array containg key/value pairs to be added
     * @return void
     */
    function adds($object)
    {
        if (is_object($object)) {
            $object = get_object_vars($object);
        }
        if (is_array($object)) {
            foreach ($object as $key => $val) {
                $this->variables[$key] = $val;
            }
        }
    }

    /**
     * Method to retrieve a corresponding value to a given key
     *
     * @param string $key
     * @return string Returns value to a given key
     */
    function get($key)
    {
        if (isset($this->variables[$key])) {
            return $this->variables[$key];
        }
    }


    /**
     * Method to retrieve an object containing a key/value paris
     *
     * @return Object Returns an object containing key/value pairs
     */
    function gets()
    {
        $num_args = func_num_args();
        $args_list = func_get_args();
        $output = new stdClass();
        for ($i = 0; $i < $num_args; $i++) {
            $key = $args_list[$i];
            $output->{$key} = $this->get($key);
        }
        return $output;
    }

    /**
     * Method to retrieve an array of key/value pairs
     *
     * @return array
     */
    function getVariables()
    {
        return $this->variables;
    }

    /**
     * Method to retrieve an object of key/value pairs
     *
     * @return Object
     */
    function getObjectVars()
    {
        $output = new stdClass();
        foreach ($this->variables as $key => $val) {
            $output->{$key} = $val;
        }
        return $output;
    }

    /**
     * Method to return either true or false depnding on the value in a 'error' variable
     *
     * @return bool Retruns true : error isn't 0 or false : otherwise.
     */
    function toBool()
    {
        // TODO This method is misleading in that it returns true if error is 0, which should be true in boolean representation.
        return $this->error == 0 ? true : false;
    }


    /**
     * Method to return either true or false depnding on the value in a 'error' variable
     *
     * @return bool
     */
    function toBoolean()
    {
        return $this->toBool();
    }

    //endregion

    var $mid = null; ///< string to represent run-time instance of Module (Karybu Module)
    var $module = null; ///< Class name of Karybu Module that is identified by mid
    var $module_srl = null; ///< integer value to represent a run-time instance of Module (Karybu Module)
    var $module_info = null; ///< an object containing the module information
    var $origin_module_info = null;
    var $xml_info = null; ///< an object containing the module description extracted from XML file

    var $module_path = null; ///< a path to directory where module source code resides

    var $act = null; ///< a string value to contain the action name

    var $template_path = null; ///< a path of directory where template files reside
    var $template_file = null; ///< name of template file

    var $layout_path = ''; ///< a path of directory where layout files reside
    var $layout_file = ''; ///< name of layout file
    var $edited_layout_file = ''; ///< name of temporary layout files that is modified in an admin mode

    var $stop_proc = false; ///< a flag to indicating whether to stop the execution of code.

    var $module_config = null;
    var $ajaxRequestMethod = array('XMLRPC', 'JSON');

    var $gzhandler_enable = true;

    /**
     * setter to set the name of module
     * @param string $module name of module
     * @return void
     **/
    function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * setter to set the name of module path
     * @param string $path the directory path to a module directory
     * @return void
     **/
    function setModulePath($path)
    {
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        $this->module_path = $path;
    }

    /**
     * setter to set an url for redirection
     * @param string $url url for redirection
     * @remark redirect_url is used only for ajax requests
     * @return void|mixed
     **/
    function setRedirectUrl($url = './', $output = null)
    {
        $ajaxRequestMethod = array_flip($this->ajaxRequestMethod);
        if (!isset($ajaxRequestMethod[Context::getRequestMethod()])) {
            $this->add('redirect_url', $url);
        }

        if ($output !== null && is_object($output)) {
            return $output;
        }
    }

    /**
     * get url for redirection
     * @return string redirect_url
     **/
    function getRedirectUrl()
    {
        return $this->get('redirect_url');
    }

    /**
     * set message
     * @param string $message a message string
     * @param string $type type of message (error, info, update)
     * @return void
     **/
    function setMessage($message, $type = null)
    {
        if (Context::getLang($message)) {
            $message = Context::getLang($message);
        }
        $this->message = $message;
        $this->setMessageType($type);
    }

    /**
     * set type of message
     * @param string $type type of message (error, info, update)
     * @return void
     **/
    function setMessageType($type)
    {
        $this->add('message_type', $type);
    }

    /**
     * get type of message
     * @return string $type
     **/
    function getMessageType()
    {
        $type = $this->get('message_type');
        $typeList = array('error' => 1, 'info' => 1, 'update' => 1);
        if (!isset($typeList[$type])) {
            $type = $this->getError() ? 'error' : 'info';
        }
        return $type;
    }

    /**
     * sett to set the template path for refresh.html
     * refresh.html is executed as a result of method execution
     * Tpl as the common run of the refresh.html ..
     * @return void
     **/
    function setRefreshPage()
    {
        $this->setTemplatePath('./common/tpl');
        $this->setTemplateFile('refresh');
    }


    /**
     * sett to set the action name
     * @param string $act
     * @return void
     **/
    function setAct($act)
    {
        $this->act = $act;
    }

    /**
     * sett to set module information
     * @param object $module_info object containing module information
     * @param object $xml_info object containing module description
     * @return void
     **/
    function setModuleInfo($module_info, $xml_info)
    {
        // The default variable settings
        $this->mid = $module_info->mid;
        $this->module_srl = @$module_info->module_srl;
        $this->module_info = $module_info;
        $this->origin_module_info = $module_info;
        $this->xml_info = $xml_info;
        if (isset($module_info->skin_vars)) {
            $this->skin_vars = $module_info->skin_vars;
        }
        // validate certificate info and permission settings necessary in Web-services
        $is_logged = Context::get('is_logged');
        $logged_info = Context::get('logged_info');
        // module model create an object
        $oModuleModel = & getModel('module');
        // permission settings. access, manager(== is_admin) are fixed and privilege name in Karybu
        $module_srl = Context::get('module_srl');
        if (!$module_info->mid && !is_array($module_srl) && preg_match('/^([0-9]+)$/', $module_srl)) {
            $request_module = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
            if (isset($request_module->module_srl) && $request_module->module_srl == $module_srl) {
                $grant = $oModuleModel->getGrant($request_module, $logged_info);
            }
        } else {
            $grant = $oModuleModel->getGrant($module_info, $logged_info, $xml_info);
            // have at least access grant
            if (substr_count($this->act, 'Member') || substr_count($this->act, 'Communication')) {
                $grant->access = 1;
            }
        }
        // display no permission if the current module doesn't have an access privilege
        //if(!$grant->access) return $this->stop("msg_not_permitted");
        // checks permission and action if you don't have an admin privilege
        if (empty($grant->manager)) {
            // get permission types(guest, member, manager, root) of the currently requested action
            if (isset($xml_info->permission) && isset($xml_info->permission->{$this->act})) {
                $permission_target = $xml_info->permission->{$this->act};
            }
            // check manager if a permission in module.xml otherwise action if no permission
            if (empty($permission_target) && substr_count($this->act, 'Admin')) {
                $permission_target = 'manager';
            }
            // Check permissions
            if (isset($permission_target)) {
                switch ($permission_target) {
                    case 'root' :
                    case 'manager' :
                        $this->stop('msg_is_not_administrator');
                        return;
                    case 'member' :
                        if (!$is_logged) {
                            $this->stop('msg_not_permitted_act');
                            return;
                        }
                        break;
                }
            }
        }
        // permission variable settings
        if (!isset($grant)) {
            $grant = null;
        }
        $this->grant = $grant;

        Context::set('grant', $grant);

        $this->module_config = $oModuleModel->getModuleConfig($this->module, $module_info->site_srl);

        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     * set the stop_proc and approprate message for msg_code
     * @param string $msg_code an error code
     * @return ModuleObject $this
     **/
    function stop($msg_code)
    {
        // flag setting to stop the proc processing
        $this->stop_proc = true;
        // Error handling
        $this->setError(-1);
        $this->setMessage($msg_code);
        // Error message display by message module
        $type = Mobile::isFromMobilePhone() ? 'mobile' : 'view';
        $oMessageObject = ModuleHandler::getModuleInstance('message', $type);
        $oMessageObject->setError(-1);
        $oMessageObject->setMessage($msg_code);
        $oMessageObject->dispMessage();

        $this->setTemplatePath($oMessageObject->getTemplatePath());
        $this->setTemplateFile($oMessageObject->getTemplateFile());

        return $this;
    }

    /**
     * set the file name of the template file
     * @param string name of file
     * @return void
     **/
    function setTemplateFile($filename)
    {
        if (substr($filename, -5) != '.html') {
            $filename .= '.html';
        }
        $this->template_file = $filename;
    }

    /**
     * retrieve the directory path of the template directory
     * @return string
     **/
    function getTemplateFile()
    {
        return $this->template_file;
    }

    /**
     * set the directory path of the template directory
     * @param string path of template directory.
     * @return void
     **/
    function setTemplatePath($path)
    {
        if (substr($path, 0, 1) != '/' && substr($path, 0, 2) != './') {
            $path = './' . $path;
        }
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        $this->template_path = $path;
    }

    /**
     * retrieve the directory path of the template directory
     * @return string
     **/
    function getTemplatePath()
    {
        return $this->template_path;
    }

    /**
     * set the file name of the temporarily modified by admin
     * @param string name of file
     * @return void
     **/
    function setEditedLayoutFile($filename)
    {
        if (substr($filename, -5) != '.html') {
            $filename .= '.html';
        }
        $this->edited_layout_file = $filename;
    }

    /**
     * retreived the file name of edited_layout_file
     * @return string
     **/
    function getEditedLayoutFile()
    {
        return $this->edited_layout_file;
    }

    /**
     * set the file name of the layout file
     * @param string name of file
     * @return void
     **/
    function setLayoutFile($file)
    {
        $this->layout_file = $file;
    }

    /**
     * get the file name of the layout file
     * @return string
     **/
    function getLayoutFile()
    {
        return $this->layout_file;
    }

    /**
     * set the directory path of the layout directory
     * @param string path of layout directory.
     **/
    function setLayoutPath($path)
    {
        if (substr($path, 0, 1) != '/' && substr($path, 0, 2) != './') {
            $path = './' . $path;
        }
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        $this->layout_path = $path;
    }

    /**
     * set the directory path of the layout directory
     * @return string
     **/
    function getLayoutPath($layout_name = null, $layout_type = "P")
    {
        return $this->layout_path;
    }

    function preProc()
    {
        // pass if stop_proc is true
        if ($this->stop_proc) {
            return false;
        }
        //all proc must be called by post
        if (isset($this->xml_info->action->{$this->act}) && method_exists($this, $this->act)) {
            $info = $this->xml_info->action->{$this->act};
            if (isset($info->type) && $info->type == 'controller'){
                $requestMethod = Context::getRequestMethod();
                if ($requestMethod == "GET" && $this->act != 'procMemberSnsSignIn') {
                    $this->stop("msg_not_permitted_act");
                    return false;
                }
            }
        }
        // trigger call
        $triggerOutput = ModuleHandler::triggerCall('moduleObject.proc', 'before', $this);
        if (!$triggerOutput->toBool()) {
            $this->setError($triggerOutput->getError());
            $this->setMessage($triggerOutput->getMessage());
            return false;
        }

        // execute an addon(call called_position as before_module_proc)
        $called_position = 'before_module_proc';
        $oAddonController = & getController('addon');
        $addon_file = $oAddonController->getCacheFilePath(Mobile::isFromMobilePhone() ? "mobile" : "pc");
        include($addon_file);

        // We are checking act again because it might have been overriden in triggers / addons
        if (isset($this->xml_info->action->{$this->act}) && method_exists($this, $this->act)) {
            // Check permissions
            if ($this->module_srl && !$this->grant->access) {
                $this->stop("msg_not_permitted_act");
                return false;
            }
            // integrate skin information of the module(change to sync skin info with the target module only by seperating its table)
            $oModuleModel = & getModel('module');
            $oModuleModel->syncSkinInfoToModuleInfo($this->module_info);
            Context::set('module_info', $this->module_info);

        } else {
            return false;
        }
    }

    function postProc($output)
    {
        // trigger call
        $triggerOutput = ModuleHandler::triggerCall('moduleObject.proc', 'after', $this);
        if (!$triggerOutput->toBool()) {
            $this->setError($triggerOutput->getError());
            $this->setMessage($triggerOutput->getMessage());
            return false;
        }

        // execute an addon(call called_position as after_module_proc)
        $called_position = 'after_module_proc';
        $oAddonController = & getController('addon');
        $addon_file = $oAddonController->getCacheFilePath(Mobile::isFromMobilePhone() ? "mobile" : "pc");
        if (file_exists($addon_file)) {
            include($addon_file);
        }

        if (is_a($output, 'Object') || is_subclass_of($output, 'Object')) {
            $this->setError($output->getError());
            $this->setMessage($output->getMessage());

            if (!$output->toBool()) {
                return false;
            }
        }
        // execute api methods of the module if view action is and result is XMLRPC or JSON
        if (isset($this->module_info->module_type) && $this->module_info->module_type == 'view') {
            if (Context::getResponseMethod() == 'XMLRPC' || Context::getResponseMethod() == 'JSON') {
                $oAPI = getAPI($this->module_info->module, 'api');
                if (method_exists($oAPI, $this->act)) {
                    $oAPI->{$this->act}($this);
                }
            }
        }
    }

    /**
     * excute the member method specified by $act variable
     * @return boolean true : success false : fail
     **/
    function proc()
    {
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $controller = array($this, $this->act);
        $request = Context::get('request');
        $resolver = new \Karybu\HttpKernel\Controller\ControllerResolver();
        $arguments = $resolver->getArguments($request, $controller);
        $output = call_user_func_array($controller, $arguments);
        return $output;
    }
}
