<?php
/**
 * @class ModuleHandler
 * @author NHN (developers@xpressengine.com)
 * Handling modules
 *
 * @remarks This class is to excute actions of modules.
 *          Constructing an instance without any parameterconstructor, it finds the target module based on Context.
 *          If there is no act on the found module, excute an action referencing action_forward.
 **/

require_once 'ModuleMatcher.class.php';

use Karybu\Validator\ValidatorSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Karybu\ModuleHandler\ModuleInstanceRetriever;
use Karybu\Exception\ModuleDoesNotExistException;
use Karybu\Exception\DefaultUrlNotDefinedException;
use Karybu\Exception\InvalidRequestException;

class ModuleHandlerInstance extends Handler
{

    var $module = null; ///< Module
    var $act = null; ///< action
    var $mid = null; ///< Module ID
    var $document_srl = null; ///< Document Number
    var $module_srl = null; ///< Module Number

    var $module_info = null; ///< Module Info. Object

    var $error = null; ///< an error code.
    var $httpStatusCode = null; ///< http status code.

    /** @var \|\ContextInstance|null */
    private $context = null;

    /** @var null MobileInstance */
    private $mobile = null;

    /** @var null Karybu\Validator\ValidatorSession */
    private $validator_session = null;

    /**
     * prepares variables to use in moduleHandler
     * @param string $module name of module
     * @param string $act name of action
     * @param int $mid
     * @param int $document_srl
     * @param int $module_srl
     * @return void
     **/
    function ModuleHandlerInstance(
        ContextInstance $context = null,
        MobileInstance $mobile = null,
        ValidatorSession $validator_session = null,
        $module = '',
        $act = '',
        $mid = '',
        $document_srl = '',
        $module_srl = ''
    ) {
        if ($context == null) {
            $context = Context::getInstance();
        }

        $this->context = $context;

        if ($mobile == null) {
            $mobile = Mobile::getInstance();
        }

        $this->mobile = $mobile;

        if($validator_session == null) {
            $validator_session = new \Karybu\Validator\ValidatorSession();
        }

        $this->validator_session = $validator_session;

        // If XE has not installed yet, set module as install
        if (!$this->context->isInstalled()) {
            $this->module = 'install';
            $this->act = $this->context->get('act');
            return;
        }

        if ($this->context->isSuccessInit == false) {
            $this->error = 'msg_invalid_request';
            return;
        }

        // Set variables from request arguments
        $this->module = $module ? $module : $this->context->get('module');
        $this->act = $act ? $act : $this->context->get('act');
        $this->mid = $mid ? $mid : $this->context->get('mid');
        $this->document_srl = $document_srl ? (int)$document_srl : (int)$this->context->get('document_srl');
        $this->module_srl = $module_srl ? (int)$module_srl : (int)$this->context->get('module_srl');
        $this->entry = $this->context->convertEncodingStr($this->context->get('entry'));
    }

    public function &getModuleModel()
    {
        return getModel('module');
    }

    public function &getDocumentModel()
    {
        return getModel('document');
    }

    private function isDisplayAction()
    {
        return isset($this->act) && substr($this->act, 0, 4) == 'disp';
    }

    private function currentRequestIsHttps()
    {
        return $this->context->getServerRequestHttps() == 'on';
    }

    private function sslEnabledAndSslActionExists()
    {
        if ($this->isDisplayAction()
            && $this->context->get('_use_ssl') == 'optional'
            && $this->context->isExistsSSLAction($this->act)
            && !$this->currentRequestIsHttps()
        ) {
            $redirect_url = 'https://' . $this->context->getServerHost() . $this->context->getServerRequestUri();
            return new \Symfony\Component\HttpFoundation\RedirectResponse($redirect_url);
        }

        return null;
    }

    private function onMainWebsiteAndDefaultModuleBelongsToVirtualSite($current_site_srl, $default_module_site_srl)
    {
        if ($current_site_srl == 0 && $default_module_site_srl > 0) {
            // Get info of virtual site
            $site_info = $this->getModuleModel()->getSiteInfo($default_module_site_srl);
            // Redirect to its domain
            $redirect_url = $this->context->getNotEncodedSiteUrl($site_info->domain, 'mid', $site_info->mid);
            return new RedirectResponse($redirect_url);
        }
    }

    /**
     * Initialization. It finds the target module based on module, mid, document_srl, and prepares to execute an action
     * @return boolean true: OK, false: redirected
     **/
    function init()
    {
        // Exit if invalid input found
        $this->validateVariablesAgainstXSS();

        // Exit if ssl is enabled and a ssl action exists for this request
        if ($response = $this->sslEnabledAndSslActionExists()) {
            return $response;
        }

        // TODO Replace with event dispatcher
        $this->executeAddon_before_module_init();

        $oModuleModel = $this->getModuleModel();
        $site_module_info = $this->context->get('site_module_info');

        try {
            if (!$this->module || $this->mid) {
                // Get current module instance properties
                $moduleInstanceRetriever = new ModuleInstanceRetriever($this->getDocumentModel(), $this->getModuleModel(
                ), $this->context);
                $result = $moduleInstanceRetriever->findModuleInfo(
                    $this->mid,
                    $this->entry,
                    $this->document_srl,
                    $site_module_info
                );
                if (!$result->isSuccessful()) {
                    return new RedirectResponse($result->getRedirectUrl());
                }

                $module_info = $result->getModuleInfo();

                // If module info was set, retrieve variables from the module information
                $this->module = $module_info->module;
                $this->mid = $module_info->mid;
                $this->module_info = $module_info;
                $this->module_info->site_srl = $site_module_info->site_srl;


                $this->context->setBrowserTitle($module_info->browser_title);

                if ($module_info->use_mobile && $this->mobile->isFromMobilePhone()) {
                    $layoutSrl = $module_info->mlayout_srl;
                } else {
                    $layoutSrl = $module_info->layout_srl;
                }

                $part_config = $oModuleModel->getModulePartConfig('layout', $layoutSrl);
                if (isset($part_config->header_script)) {
                    $this->context->addHtmlHeader($part_config->header_script);
                }
            }

        } catch (ModuleDoesNotExistException $e) {
            $this->error = $e->getMessage();
            $this->httpStatusCode = '404';
        } catch (DefaultUrlNotDefinedException $e) {
            $this->error = $e->getMessage();
            $this->httpStatusCode = '500';
            return false;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            $this->httpStatusCode = '500';
        }

        if (!$this->module_info) {
            $this->module_info = new stdClass();
            $this->module_info->module = $this->module;
            $this->module_info->mid = $this->mid;
            $this->module_info->site_srl = $site_module_info->site_srl;
        }

        // If mid exists, set mid into context
        if ($this->mid) {
            $this->context->set('mid', $this->mid, true);
        }
        if ($this->document_srl) {
            $this->context->set('document_srl', $this->document_srl);
        }

        // Call a trigger after moduleHandler init
        // TODO Replace with event dispatcher
        $output = $this->triggerCall('moduleHandler.init', 'after', $this->module_info);
        if (!$output->toBool()) {
            $this->error = $output->getMessage();
            return false;
        }

        // Set current module info into context
        $this->context->set('current_module_info', $this->module_info);

        return true;
    }

    public function executeAddon_before_module_init()
    {
        // execute addon (before module initialization)
        $called_position = 'before_module_init';
        $oAddonController = & getController('addon');
        $addon_file = $oAddonController->getCacheFilePath($this->mobile->isFromMobilePhone() ? 'mobile' : 'pc');
        include($addon_file);
    }

    public function validateVariablesAgainstXSS()
    { // Validate variables to prevent XSS
        $isInvalid = null;
        if ($this->module && !preg_match("/^([a-z0-9\_\-]+)$/i", $this->module)) {
            $isInvalid = true;
        }
        if ($this->mid && !preg_match("/^([a-z0-9\_\-]+)$/i", $this->mid)) {
            $isInvalid = true;
        }
        if ($this->act && !preg_match("/^([a-z0-9\_\-]+)$/i", $this->act)) {
            $isInvalid = true;
        }
        if ($isInvalid) {
            $this->printInvalidRequestHtmlPage();
            $this->context->close();
            $this->terminateScript();
        }
    }

    public function terminateScript()
    {
        exit;
    }

    public function printInvalidRequestHtmlPage()
    {
        htmlHeader();
        echo $this->context->getLang("msg_invalid_request");
        htmlFooter();
    }

    protected function showErrorToUser()
    {
        $type = $this->mobile->isFromMobilePhone() ? 'mobile' : 'view';
        $oMessageObject = & ModuleHandler::getModuleInstance('message', $type);
        $oMessageObject->setError(-1);
        $oMessageObject->setMessage($this->error);
        $oMessageObject->dispMessage();
        if ($this->httpStatusCode) {
            $oMessageObject->setHttpStatusCode($this->httpStatusCode);
        }
        return $oMessageObject;
    }


    function checkForErrorsAndPrepareMobileStatus()
    {
        // If error occurred while preparation, return a message instance
        if ($this->error) {
            return $this->showErrorToUser();
        }
        if ($this->module_info->use_mobile != "Y") {
            Mobile::setMobile(false);
        }
    }


    function getController()
    {
        $resolver = new \Karybu\HttpKernel\Controller\ControllerResolver();
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = Context::get('request');
        $request->attributes->set("act", $this->act);
        $request->attributes->set("module", $this->module);
        $request->attributes->set("is_mobile", Mobile::isFromMobilePhone());
        $request->attributes->set("is_installed", Context::isInstalled());
        $request->attributes->set('module_info', $this->module_info);

        try {
            // Get ModuleObject instance
            $controller = $resolver->getController($request);
        } catch (ModuleDoesNotExistException $e) {
            $this->error = 'msg_module_is_not_exists';
            $this->httpStatusCode = '404';
            return $this->showErrorToUser();
        }
        catch (InvalidRequestException $e) {
            $this->error = 'msg_invalid_request';
            return $this->showErrorToUser();
        }
            // TODO Catch proper exceptions
        catch (Exception $e) {
            $this->error = 'msg_invalid_request';
            return $this->showErrorToUser();
        }

        // Return $oModule instance - see ControllerResolver::getController for info
        return $controller[0];
    }

    protected function loadAdminLayoutAndMenuForModule(&$oModule)
    {
        $oAdminView = & getAdminView('admin');
        $oAdminView->makeGnbUrl($oModule->module_key->getModule());
        $oAdminView->makeDashboardSitemap();
        $oModule->setLayoutPath("./modules/admin/tpl");
        $oModule->setLayoutFile("layout.html");
        $this->context->addBodyClass('x');
    }

    public function checkUserPermissions($oModule)
    {
        $this->act = $oModule->act;

        $oModuleModel = $this->getModuleModel();
        // Now that we have a module instance and a valid act, do stuff
        $logged_info = $this->context->get('logged_info');
        if (isset($oModule->checkAdminPermission)
            && $this->module == "admin"
            && $oModule->module_key->getType() == "view"
        ) {
            // This is basically the entry point to the admin interface
            if ($logged_info->is_admin == 'Y') {
                // Load the admin layout
                if ($this->act != 'dispLayoutAdminLayoutModify') {
                    $this->loadAdminLayoutAndMenuForModule($oModule);
                }
            } else {
                $this->error = 'msg_is_not_administrator';
                return $this->showErrorToUser();
            }
        }

        if ($oModule->module_key->isAdmin()) {
            $this->initAdminMenu();
            if ($_SESSION['denied_admin'] == 'Y') {
                $this->error = "msg_not_permitted_act";
                return $this->showErrorToUser();
            }

            if ($oModule->checkAdminPermission) {
                // Validate current user permissions
                $grant = $oModuleModel->getGrant($this->module_info, $logged_info);
                if (!$grant->is_admin && !$grant->manager) {
                    $this->error = 'msg_is_not_manager';
                    return $this->showErrorToUser();
                }
            }
        }

        unset($logged_info);
    }

    public function injectCustomHeaderAndFooter($oModule)
    {
        if ($oModule->module_key->getType(
        ) == "view" && $this->module_info->use_mobile == "Y" && $this->mobile->isMobileCheckByAgent()
        ) {
            global $lang;
            $header = '<style type="text/css">div.xe_mobile{opacity:0.7;margin:1em 0;padding:.5em;background:#333;border:1px solid #666;border-left:0;border-right:0}p.xe_mobile{text-align:center;margin:1em 0}a.xe_mobile{color:#ff0;font-weight:bold;font-size:24px}@media only screen and (min-width:500px){a.xe_mobile{font-size:15px}}</style>';
            $footer = '<div class="xe_mobile"><p class="xe_mobile"><a class="xe_mobile" href="' . getUrl(
                'm',
                '1'
            ) . '">' . $lang->msg_pc_to_mobile . '</a></p></div>';
            $this->context->addHtmlHeader($header);
            $this->context->addHtmlFooter($footer);
        }

        if ($oModule->module_key->getType() == "view" && $oModule->module_key->getKind() != 'admin') {
            $oModuleModel = $this->getModuleModel();
            $module_config = $oModuleModel->getModuleConfig('module');
            if (isset($module_config->htmlFooter)) {
                $this->context->addHtmlFooter($module_config->htmlFooter);
            }
        }

        // if failed message exists in session, set context
        $this->validator_session->setErrorsToContext();
    }

    /**
     * - Checks user permissions
     * - Checks rulesets
     *
     */
    function filterController($oModule)
    {
        $output = $this->checkUserPermissions($oModule);
        if($output) return $output;

        $this->injectCustomHeaderAndFooter($oModule);
    }

    function setErrorsToSessionAfterProc($oModule, $procResult)
    {
        $methodList = array('XMLRPC' => 1, 'JSON' => 1);
        if (!$oModule->stop_proc && !isset($methodList[Context::getRequestMethod()])) {
            $error = $oModule->getError();
            $message = $oModule->getMessage();
            $messageType = $oModule->getMessageType();
            $redirectUrl = $oModule->getRedirectUrl();

            if ($procResult === false) {
                $this->error = $message;
                if (!$redirectUrl && Context::get('error_return_url')) {
                    $redirectUrl = Context::get('error_return_url');
                }
                $this->validator_session->saveRequestVariables();

            } else {
                if (count($_SESSION['INPUT_ERROR'])) {
                    Context::set('INPUT_ERROR', $_SESSION['INPUT_ERROR']);
                    $_SESSION['INPUT_ERROR'] = '';
                }
            }

            $_SESSION['XE_VALIDATOR_ERROR'] = $error;
            if ($message != 'success') {
                $_SESSION['XE_VALIDATOR_MESSAGE'] = $message;
            }
            $_SESSION['XE_VALIDATOR_MESSAGE_TYPE'] = $messageType;

            if (Context::get('xeVirtualRequestMethod') != 'xml') {
                $_SESSION['XE_VALIDATOR_RETURN_URL'] = $redirectUrl;
            }
        }
    }

    /**
     * get a module instance and execute an action
     * @return ModuleObject executed module instance
     **/
    function procModule()
    {

        $this->checkForErrorsAndPrepareMobileStatus();

        // Get info necessary to retrieve the module's instance
        /** @var $oModule \ModuleObject */
        $oModule = $this->getController(); // ControllerResolver::getController

        $this->filterController($oModule);

        // controller callback: array(get_class($oModule), $this->act)
        if ($oModule->preProc()) {
            $output = $oModule->proc(); // Controller::execute
            $procResult = (bool)$oModule->postProc($output);
        } else {
            $procResult = false;
        }

        $this->setErrorsToSessionAfterProc($oModule, $procResult);

        return $oModule;
    }

    public function getModuleInstanceFromKeyAndAct(ModuleKey $key, $act)
    {
        // 5. Get the instance
        $oModule = $this->getModuleInstance($key->getModule(), $key->getType(), $key->getKind());

        // 5.1. If module wasn't found and we're on mobile platform, we try to fallback to classic view
        if ($key->getType() == 'mobile' && (!is_object($oModule) || !method_exists($oModule, $act))) {
            $key = new ModuleKey($key->getModule(), 'view', $key->getKind());
            $this->mobile->setMobile(false);
            $oModule = $this->getModuleInstance($key->getModule(), $key->getType(), $key->getKind());
        }
        return $oModule;
    }

    protected function initAdminMenu()
    {
        // admin menu check
        if (Context::isInstalled()) {
            $oMenuAdminModel = & getAdminModel('menu');
            $output = $oMenuAdminModel->getMenuByTitle('__XE_ADMIN__');

            if (!$output->menu_srl) {
                $oAdminClass = & getClass('admin');
                $oAdminClass->createXeAdminMenu();
            } else {
                if (!is_readable($output->php_file)) {
                    $oMenuAdminController = & getAdminController('menu');
                    $oMenuAdminController->makeXmlFile($output->menu_srl);
                }
            }
        }
    }




    /**
     * display contents from executed module
     * @param ModuleObject $oModule module instance
     * @return void
     **/
    function displayContent($oModule = null)
    {
        // If the module is not set or not an object, set error
        if (!$oModule || !is_object($oModule)) {
            $this->error = 'msg_module_is_not_exists';
            $this->httpStatusCode = '404';
        }

        // If connection to DB has a problem even though it's not install module, set error
        if ($this->module != 'install' && $GLOBALS['__DB__'][$this->context->getDBType()]->isConnected() == false) {
            $this->error = 'msg_dbconnect_failed';
        }

        // Call trigger after moduleHandler proc
        $output = ModuleHandler::triggerCall('moduleHandler.proc', 'after', $oModule);
        if (!$output->toBool()) {
            $this->error = $output->getMessage();
        }

        // Use message view object, if HTML call
        $methodList = array('XMLRPC' => 1, 'JSON' => 1);
        if (!isset($methodList[$this->context->getRequestMethod()])) {

            if ($_SESSION['XE_VALIDATOR_RETURN_URL']) {
                //_debugOutput refactored as Response listener
                // $display_handler = new DisplayHandler();
                // $display_handler->_debugOutput();

                header('location:' . $_SESSION['XE_VALIDATOR_RETURN_URL']);
                return;
            }

            // If error occurred, handle it
            if ($this->error) {
                // display content with message module instance
                $type = $this->mobile->isFromMobilePhone() ? 'mobile' : 'view';
                $oMessageObject = & ModuleHandler::getModuleInstance('message', $type);
                $oMessageObject->setError(-1);
                $oMessageObject->setMessage($this->error);
                $oMessageObject->dispMessage();

                if ($oMessageObject->getHttpStatusCode() && $oMessageObject->getHttpStatusCode() != '200') {
                    $this->_setHttpStatusMessage($oMessageObject->getHttpStatusCode());
                    $oMessageObject->setTemplateFile('http_status_code');
                }

                // If module was called normally, change the templates of the module into ones of the message view module
                if ($oModule) {
                    $oModule->setTemplatePath($oMessageObject->getTemplatePath());
                    $oModule->setTemplateFile($oMessageObject->getTemplateFile());
                    // Otherwise, set message instance as the target module
                } else {
                    $oModule = $oMessageObject;
                }

                $this->validator_session->clear();
            }

            // Check if layout_srl exists for the module
            if ($this->mobile->isFromMobilePhone()) {
                $layout_srl = $oModule->module_info->mlayout_srl;
            } else {
                $layout_srl = $oModule->module_info->layout_srl;
            }

            if ($layout_srl && !$oModule->getLayoutFile()) {

                // If layout_srl exists, get information of the layout, and set the location of layout_path/ layout_file
                $oLayoutModel = & getModel('layout');
                $layout_info = $oLayoutModel->getLayout($layout_srl);
                if ($layout_info) {

                    // Input extra_vars into $layout_info
                    if ($layout_info->extra_var_count) {

                        foreach ($layout_info->extra_var as $var_id => $val) {
                            if ($val->type == 'image') {
                                if (preg_match(
                                    '/^\.\/files\/attach\/images\/(.+)/i',
                                    $val->value
                                )
                                ) {
                                    $val->value = $this->context->getRequestUri() . substr($val->value, 2);
                                }
                            }
                            $layout_info->{$var_id} = $val->value;
                        }
                    }
                    // Set menus into context
                    if ($layout_info->menu_count) {
                        foreach ($layout_info->menu as $menu_id => $menu) {
                            if (file_exists($menu->php_file)) {
                                include($menu->php_file);
                            }
                            $this->context->set($menu_id, $menu);
                        }
                    }

                    // Set layout information into context
                    $this->context->set('layout_info', $layout_info);

                    $oModule->setLayoutPath($layout_info->path);
                    $oModule->setLayoutFile('layout');

                    // If layout was modified, use the modified version
                    $edited_layout = $oLayoutModel->getUserLayoutHtml($layout_info->layout_srl);
                    if (file_exists($edited_layout)) {
                        $oModule->setEditedLayoutFile($edited_layout);
                    }
                }
            }
        }
    }

    /**
     * returns module's path
     * @param string $module module name
     * @return string path of the module
     **/
    function getModulePath($module)
    {
        return sprintf('./modules/%s/', $module);
    }

    /**
     * It creates a module instance
     * @param string $module module name
     * @param string $type instance type, (e.g., view, controller, model)
     * @param string $kind admin or svc
     * @return ModuleObject module instance (if failed it returns null)
     * @remarks if there exists a module instance created before, returns it.
     **/
    function &getModuleInstance($module, $type = 'view', $kind = '')
    {

        if (__DEBUG__ == 3) {
            $start_time = getMicroTime();
        }

        $parent_module = $module;
        $kind = strtolower($kind);
        $type = strtolower($type);

        $kinds = array('svc' => 1, 'admin' => 1);
        if (!isset($kinds[$kind])) {
            $kind = 'svc';
        }

        $key = $module . '.' . ($kind != 'admin' ? '' : 'admin') . '.' . $type;

        if (isset($GLOBALS['__MODULE_EXTEND__'])) {
            if (is_array($GLOBALS['__MODULE_EXTEND__']) && array_key_exists($key, $GLOBALS['__MODULE_EXTEND__'])) {
                $module = $extend_module = $GLOBALS['__MODULE_EXTEND__'][$key];
            }
        }


        // if there is no instance of the module in global variable, create a new one
        if (!isset($GLOBALS['_loaded_module'][$module][$type][$kind])) {
            ModuleHandler::_getModuleFilePath(
                $module,
                $type,
                $kind,
                $class_path,
                $high_class_file,
                $class_file,
                $instance_name
            );

            if (isset($extend_module)) {
                if ($extend_module && (!is_readable($high_class_file) || !is_readable($class_file))) {
                    $module = $parent_module;
                    ModuleHandler::_getModuleFilePath(
                        $module,
                        $type,
                        $kind,
                        $class_path,
                        $high_class_file,
                        $class_file,
                        $instance_name
                    );
                }
            }

            // Get base class name and load the file contains it
            if (!class_exists($module)) {
                $high_class_file = sprintf('%s%s%s.class.php', _XE_PATH_, $class_path, $module);
                if (!file_exists($high_class_file)) {
                    return null;
                }
                require_once($high_class_file);
            }

            // Get the name of the class file
            if (!is_readable($class_file)) {
                return null;
            }

            // Create an instance with eval function
            require_once($class_file);
            if (!class_exists($instance_name)) {
                return null;
            }
            $tmp_fn = create_function('', "return new {$instance_name}();");
            $oModule = $tmp_fn();
            if (!is_object($oModule)) {
                return null;
            }

            // Load language files for the class
            $this->context->loadLang($class_path . 'lang');
            if (isset($extend_module)) {
                $this->context->loadLang(ModuleHandler::getModulePath($parent_module) . 'lang');
            }

            // Set variables to the instance
            $oModule->setModule($module);
            $oModule->setModulePath($class_path);

            // If the module has a constructor, run it.
            if (!isset($GLOBALS['_called_constructor'][$instance_name])) {
                $GLOBALS['_called_constructor'][$instance_name] = true;
                if (@method_exists($oModule, $instance_name)) {
                    $oModule->{$instance_name}();
                }
            }

            // Store the created instance into GLOBALS variable
            $GLOBALS['_loaded_module'][$module][$type][$kind] = $oModule;
        }

        if (__DEBUG__ == 3) {
            $GLOBALS['__elapsed_class_load__'] += getMicroTime() - $start_time;
        }

        // return the instance
        return $GLOBALS['_loaded_module'][$module][$type][$kind];
    }

    function _getModuleFilePath($module, $type, $kind, &$classPath, &$highClassFile, &$classFile, &$instanceName)
    {
        $classPath = ModuleHandler::getModulePath($module);

        $highClassFile = sprintf('%s%s%s.class.php', _XE_PATH_, $classPath, $module);
        $highClassFile = FileHandler::getRealPath($highClassFile);

        $types = explode(' ', 'view controller model api wap mobile class');
        if (!in_array($type, $types)) {
            $type = $types[0];
        }
        if ($type == 'class') {
            $instanceName = '%s';
            $classFile = '%s%s.%s.php';
        } elseif ($kind == 'admin' && array_search($type, $types) < 3) {
            $instanceName = '%sAdmin%s';
            $classFile = '%s%s.admin.%s.php';
        } else {
            $instanceName = '%s%s';
            $classFile = '%s%s.%s.php';
        }

        $instanceName = sprintf($instanceName, $module, ucfirst($type));
        $classFile = sprintf($classFile, $classPath, $module, $type);
        $classFile = FileHandler::getRealPath($classFile);
    }

    /**
     * call a trigger
     * @param string $trigger_name trigger's name to call
     * @param string $called_position called position
     * @param object $obj an object as a parameter to trigger
     * @return Object
     **/
    function triggerCall($trigger_name, $called_position, &$obj)
    {
        // skip if not installed
        if (!$this->context->isInstalled()) {
            return new Object();
        }

        $oModuleModel = & getModel('module');
        $triggers = $oModuleModel->getTriggers($trigger_name, $called_position);
        if (!$triggers || !count($triggers)) {
            return new Object();
        }

        foreach ($triggers as $item) {
            $module = $item->module;
            $type = $item->type;
            $called_method = $item->called_method;

            $oModule = null;
            $oModule = & getModule($module, $type);
            if (!$oModule || !method_exists($oModule, $called_method)) {
                continue;
            }

            $output = $oModule->{$called_method}($obj);
            if (is_object($output) && method_exists($output, 'toBool') && !$output->toBool()) {
                return $output;
            }
            unset($oModule);
        }

        return new Object();
    }

    /**
     * get http status message by http status code
     * @param string $code
     * @return string
     **/
    function _setHttpStatusMessage($code)
    {
        $statusMessageList = array(
            '100' => 'Continue',
            '101' => 'Switching Protocols',
            '201' => 'OK',
            '201' => 'Created',
            '202' => 'Accepted',
            '203' => 'Non-Authoritative Information',
            '204' => 'No Content',
            '205' => 'Reset Content',
            '206' => 'Partial Content',
            '300' => 'Multiple Choices',
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305' => 'Use Proxy',
            '307' => 'Temporary Redirect',
            '400' => 'Bad Request',
            '401' => 'Unauthorized',
            '402' => 'Payment Required',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method Not Allowed',
            '406' => 'Not Acceptable',
            '407' => 'Proxy Authentication Required',
            '408' => 'Request Timeout',
            '409' => 'Conflict',
            '410' => 'Gone',
            '411' => 'Length Required',
            '412' => 'Precondition Failed',
            '413' => 'Request Entity Too Large',
            '414' => 'Request-URI Too Long',
            '415' => 'Unsupported Media Type',
            '416' => 'Requested Range Not Satisfiable',
            '417' => 'Expectation Failed',
            '500' => 'Internal Server Error',
            '501' => 'Not Implemented',
            '502' => 'Bad Gateway',
            '503' => 'Service Unavailable',
            '504' => 'Gateway Timeout',
            '505' => 'HTTP Version Not Supported',
        );
        $statusMessage = $statusMessageList[$code];
        if (!$statusMessage) {
            $statusMessage = 'OK';
        }

        $this->context->set('http_status_code', $code);
        $this->context->set('http_status_message', $statusMessage);
    }
}

/**
 * Use ModuleHandlerInstance instead,
 * so that we remove all static calls
 *
 * @deprecated
 */
class ModuleHandler
{
    /** @var null ModuleHandlerInstance */
    private static $module_handler = null;

    public static function setModuleHandler(ModuleHandlerInstance $module_handler)
    {
        self::$module_handler = $module_handler;
    }

    public static function getModuleInstance($module, $type = 'view', $kind = '')
    {
        $module_handler = new ModuleHandlerInstance();
        return $module_handler->getModuleInstance($module, $type, $kind);
    }

    public static function _getModuleFilePath(
        $module,
        $type,
        $kind,
        &$classPath,
        &$highClassFile,
        &$classFile,
        &$instanceName
    ) {
        $module_handler = new ModuleHandlerInstance();
        return $module_handler->_getModuleFilePath(
            $module,
            $type,
            $kind,
            $classPath,
            $highClassFile,
            $classFile,
            $instanceName
        );
    }

    public static function getModulePath($module)
    {
        $module_handler = new ModuleHandlerInstance();
        return $module_handler->getModulePath($module);
    }

    public static function triggerCall($trigger_name, $called_position, &$obj)
    {
        $module_handler = new ModuleHandlerInstance();
        return $module_handler->triggerCall($trigger_name, $called_position, $obj);
    }

    public static function getModuleInstanceFromKeyAndAct(ModuleKey $key, $act)
    {
        $module_handler = new ModuleHandlerInstance();
        return $module_handler->getModuleInstanceFromKeyAndAct($key, $act);
    }
}