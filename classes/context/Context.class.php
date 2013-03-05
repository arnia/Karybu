<?php
define('FOLLOW_REQUEST_SSL',0);
define('ENFORCE_SSL',1);
define('RELEASE_SSL',2);

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Manages Context such as request arguments/environment variables
 * It has dual method structure, easy-to use methods which can be called as Context::methodname(),and methods called with static object.
 *
 * @author NHN (developers@xpressengine.com)
 */
class ContextInstance {

	/**
	 * Allow rewrite
	 * @var bool true: using rewrite mod, false: otherwise
	 */
	var $allow_rewrite = false;
	/**
	 * Request method
	 * @var string GET|POST|XMLRPC
	 */
	var $request_method  = '';
	/**
	 * Response method.If it's not set, it follows request method.
	 * @var string HTML|XMLRPC 
	 */
	var $response_method = '';
	/**
	 * Conatins request parameters and environment variables
	 * @var object
	 */
	var $context  = null;
	/**
	 * DB info 
	 * @var object
	 */
	var $db_info  = null;
	/**
	 * FTP info 
	 * @var object
	 */
	var $ftp_info = null;
	/**
	 * ssl action cache file
	 * @var array
	 */
	var $sslActionCacheFile = './files/cache/sslCacheFile.php';
	/**
	 * List of actions to be sent via ssl (it is used by javascript xml handler for ajax)
	 * @var array
	 */
	var $ssl_actions = array();
	/**
	 * obejct oFrontEndFileHandler()
	 * @var object
	 */
	var $oFrontEndFileHandler;
    /**
     * obejct FileHandler()
     * @var object
     */
    var $file_handler;
	/**
	 * script codes in <head>..</head>
	 * @var string
	 */
	var $html_header = null;
	/**
	 * class names of <body>
	 * @var array
	 */
	var $body_class  = array();
	/**
	 * codes after <body>
	 * @var string
	 */
	var $body_header = null;
	/**
	 * class names before </body>
	 * @var string
	 */
	var $html_footer = null;
	/**
	 * path of Xpress Engine 
	 * @var string
	 */
	var $path = '';

	// language information - it is changed by HTTP_USER_AGENT or user's cookie
	/**
	 * language type 
	 * @var string
	 */
	var $lang_type = '';
	/**
	 * contains language-specific data
	 * @var object 
	 */
	var $lang = null;
	/**
	 * list of loaded languages (to avoid re-loading them)
	 * @var array
	 */
	var $loaded_lang_files = array();
	/**
	 * site's browser title
	 * @var string
	 */
	var $site_title = '';
	/**
	 * variables from GET or form submit
	 * @var mixed
	 */
	var $get_vars = null;
	/**
	 * Checks uploaded 
	 * @var bool true if attached file exists
	 */
	var $is_uploaded = false;
	/**
	 * Pattern for request vars check
	 * @var array
	 */
	var $patterns = array(
			'/<\?/iUsm',
			'/<\%/iUsm',
			'/<script\s*?language\s*?=\s*?("|\')?\s*?php\s*("|\')?/iUsm'
			);
	/**
	 * Check init
	 * @var bool false if init fail
	 */
	var $isSuccessInit = true;


    public $request, $router;


    /**
    /**
     * List of enabled languages
     */
    var $lang_selected = null;
    /**
     * List of supported languages
     */
    var $lang_supported = null;

    /**
     * List of possible Request URIs
     */
    var $url = array();

    /**
     * Current site info and current url info - used only by getUrl
     * Moved here because they were static
     */
    var $site_module_info;
    var $current_info;

    /**
     * Computed script path URL
     */
    var $script_path_url;

    /**
     * Loaded javascript plugins
     */
    var $loaded_javascript_plugins;

    /**
     * Validator
     */
    var $validator;

//    /**
//	 * returns static context object (Singleton). It's to use Context without declaration of an object
//	 *
//	 * @return object Instance
//	 */
//	function &getInstance(FileHandler $file_handler = null, FrontEndFileHandler $frontend_file_handler = null) {
//		static $theInstance = null;
//		if(!$theInstance) $theInstance = new Context($file_handler, $frontend_file_handler);
//        // TODO Move this method inside Context::init and Context::init in the constructor
//        $theInstance->loadSslActionsCacheFile();
//        return $theInstance;
//	}

    public function loadSslActionsCacheFile()
    {
        // include ssl action cache file
        if ($this->sslActionsFileExists()) {
            $sslActions = $this->getSslActionsFromCacheFile();
            if (isset($sslActions)) {
                $this->ssl_actions = $sslActions;
            }
        }
    }

    public function getSslActionsFromCacheFile()
    {
        $file = $this->file_handler->getRealPath($this->sslActionCacheFile);

        $sslActions = null;
        require_once($file);
        return $sslActions;
    }

    /**
     * Cunstructor
     *
     * @return void
     */
    function ContextInstance(FileHandler $file_handler = null, FrontEndFileHandler $frontend_file_handler = null, Validator $validator = null)
    {
        if(!isset($file_handler)) $file_handler = new FileHandler();
        if(!isset($frontend_file_handler)) $frontend_file_handler = new FrontEndFileHandler();
        if(!isset($validator)) $validator = new Validator();

        $this->file_handler = $file_handler;
        $this->oFrontEndFileHandler = $frontend_file_handler;
        $this->validator = $validator;
    }

    /**
     * Returns a reference to the $GLOBALS array
     *
     * @param $key
     * @return mixed
     */
    public function &getGlobals($key)
    {
        if(!isset($GLOBALS[$key]))
            $GLOBALS[$key] = new stdClass();

        return $GLOBALS[$key];
    }

    /**
     * Returns a reference to the global $_COOKIE array
     *
     * @return mixed
     */
    public function &getGlobalCookies()
    {
        return $_COOKIE;
    }

    /**
     * Returns a reference to an element in the global $_COOKIE array
     *
     * @return mixed
     */
    public function &getGlobalCookie($key)
    {
        return $_COOKIE[$key];
    }

    /**
     * Wrapper for the session_id() function
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * Wrapper for the session_id() function
     */
    public function setSessionId($id)
    {
        return session_id($id);
    }

    /**
     * Wrapper for the session_name() function
     */
    public function getSessionName()
    {
        return session_name();
    }

    /**
     * Wrapper for the session_set_save_handler() function
     */
    public function setSessionSaveHandler($open, $close, $read, $write, $destroy, $gc)
    {
        session_set_save_handler($open, $close, $read, $write, $destroy, $gc);
    }

    /**
     * Wrapper for the session_start() function
     */
    public function startPHPSession()
    {
        return session_start();
    }


    /**
     * Wrapper for php's setcookie function
     *
     * @param $key
     * @param $value
     * @param $expire
     * @param $path
     */
    public function setCookie($key, $value, $expire = null, $path = null)
    {
        if(!isset($expire))
        {
            setcookie($key, $value);
            return;
        }

        setcookie($key, $value, $expire, $path);
    }

    /**
     * Returns a reference to the global $_REQUEST array
     *
     * @return array
     */
    public function &getRequest()
    {
        return $_REQUEST;
    }

    /**
     * Returns a reference to the global $_GET array
     */
    public function &getArgumentsForGETRequest()
    {
        return $_GET;
    }

    /**
     * Returns a reference to the global $_POST array
     */
    public function &getArgumentsForPOSTRequest()
    {
        return $_POST;
    }

    /**
     * Returns a reference to the global $_POST array
     */
    public function &getPOSTArgument($name)
    {
        $post = $this->getArgumentsForPOSTRequest();
        return $post[$name];
    }

    /**
     * Returns a reference to the global $_FILES array
     */
    public function &getFiles()
    {
        return $_FILES;
    }

    /**
     * Returns the value of $_SERVER['CONTENT_TYPE']
     * # Symfony2\Request equivalent: $request->headers->get('Content-Type');
     */
    public function getRequestContentType()
    {
        if(isset($_SERVER['CONTENT_TYPE']))
            return $_SERVER['CONTENT_TYPE'];
        return null;
    }

    /**
     * Returns $GLOBAL['HTTP_RAW_POST_DATA']
     * # Symfony2\Request equivalent: $request->getContent();
     */
    public function getRequestContent()
    {
        if(isset($GLOBALS['HTTP_RAW_POST_DATA']))
            return $GLOBALS['HTTP_RAW_POST_DATA'];
        return null;
    }

    /**
     * Returns $_SERVER['REQUEST_METHOD']
     * # Symfony2\Request equivalent: $request->getMethod();
     */
    public function getServerRequestMethod()
    {
        if(isset($_SERVER['REQUEST_METHOD']))
            return $_SERVER['REQUEST_METHOD'];
        return null;
    }

    /**
     * Returns $_SERVER['SERVER_PROTOCOL']
     * # Symfony2\Request equivalent: $request->server->get('SERVER_PROTOCOL')
     */
    public function getServerRequestProtocol()
    {
        if(isset($_SERVER['SERVER_PROTOCOL']))
            return $_SERVER['SERVER_PROTOCOL'];
        return null;
    }

    /**
     * Returns $_SERVER['HTTPS']
     * # Symfony2\Request equivalent: $request->server->get('HTTPS')
     */
    public function getServerRequestHttps()
    {
        if(isset($_SERVER['HTTPS']))
            return $_SERVER['HTTPS'];
        return null;
    }

    /**
     * Returns $_SERVER['HTTP_HOST']get
     *  # Symfony2\Request equivalent: $request->headers->get('HOST')
     */
    public function getServerHost()
    {
        if(isset($_SERVER['HTTP_HOST']))
            return $_SERVER['HTTP_HOST'];
        return null;
    }

    /**
     * Returns $_SERVER['REQUEST_URI']
     *  # Symfony2\Request equivalent:  $request->server->get('REQUEST_URI');
     */
    public function getServerRequestUri()
    {
        if(isset($_SERVER['REQUEST_URI']))
            return $_SERVER['REQUEST_URI'];
        return null;
    }

    /**
     * Returns $_SERVER['SCRIPT_NAME'], after doing some parsing on it
     * @return string
     */
    public function getScriptPath()
    {
        if($this->script_path_url == null) {
            $this->script_path_url = preg_replace('/\/tools\//i','/',preg_replace('/index.php$/i','',str_replace('\\','/',$_SERVER['SCRIPT_NAME'])));
        }
        return $this->script_path_url;
    }


    /**
     * Wrappper for php's is_uploaded_file function
     */
    public function is_uploaded_file($file_name)
    {
        return is_uploaded_file($file_name);
    }

    /**
     * Returns the installController
     */
    public function &getInstallController()
    {
        return getController('install');
    }

    /**
     * Returns the moduleController
     */
    public function &getModuleController()
    {
        return getController('module');
    }

    public function &getMemberModel()
    {
        return getModel('member');
    }

    public function &getMemberController()
    {
        return getController('member');
    }

    public function &getSessionModel()
    {
        return getModel('session');
    }

    public function &getSessionController()
    {
        return getController('session');
    }

    /**
     * Wrapper for the global isSiteID function
     *
     * @param $domain
     * @return bool
     */
    public function isSiteID($domain)
    {
        return isSiteID($domain);
    }

    /**
     * Wrapper for the global isCrawler function
     *
     * @return bool
     */
    public function isCrawler()
    {
        return isCrawler();
    }


    public function setRedirectResponseTo($url)
    {
        header('location:'.$url);
    }

    /**
     * Returns the current dbinfo
     */
    public function loadDbInfoFromConfigFile()
    {
        $config_file = $this->getConfigFile();
        $db_info = new stdClass();
        if(is_readable($config_file)) include($config_file);
        return $db_info;
    }


    /**
     * Initialization, it sets DB information, request arguments and so on.
     *
     * @see This function should be called only once
     * @return void
     */
    function init() {
        $this->linkContextToGlobals(
            $this->getGlobals('lang'),
            $this->getGlobalCookies());

        $this->initializeRequestArguments();
        $this->initializeAppSettingsAndCurrentSiteInfo();
        $this->initializeLanguages();

        $this->startSession();
        $this->loadModuleExtends();
        $this->setAuthenticationInfoInContextAndSession();

        $current_url = $this->getCurrentUrl();
        $this->set('current_url', $current_url);

        $this->set('request_uri',Context::getRequestUri());
	}

    /**
     * Returns an url to be used in client-side js scripts
     * Here's an example from a js file:
     *      location.href = current_url.setQuery('module_srl',module_srl);
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        // set locations for javascript use
        if ($this->getServerRequestMethod() == 'GET') {
            if ($this->get_vars) {
                $url = null;
                foreach ($this->get_vars as $key => $val) {
                    if (is_array($val) && count($val)) {
                        foreach ($val as $k => $v) {
                            $url .= ($url ? '&' : '') . $key . '[' . $k . ']=' . urlencode($v);
                        }
                    } elseif ($val) {
                        $url .= ($url ? '&' : '') . $key . '=' . urlencode($val);
                    }
                }
                return sprintf('%s?%s', $this->getRequestUri(), $url);
            } else {
                return $this->getUrl();
            }
        } else {
            return $this->getRequestUri();
        }
    }

    public function initializeLanguages()
    { // Load Language File
        $enabled_languages = $this->loadLangSelected();
        $this->set('lang_supported', $enabled_languages);

        $current_language = $this->getCurrentLanguage($enabled_languages, $this->db_info->lang_type);
        $this->setLangType($current_language);

        $this->lang = & $GLOBALS['lang'];
        // load module module's language file according to language setting
        $this->loadLang(_XE_PATH_ . 'modules/module/lang');
        // load common language file
        $this->loadLang(_XE_PATH_ . 'common/lang/');
    }

    public function loadModuleExtends()
    {
        if (!Context::isInstalled()) return;

        $oModuleModel = & getModel('module');
        $oModuleModel->loadModuleExtends();
    }

    /**
     * Save info of the currently logged in user in Context and Session
     */
    public function setAuthenticationInfoInContextAndSession()
    {
        if (!$this->isInstalled()) {
            return;
        }

        $oMemberModel = &$this->getMemberModel();
        $oMemberController = &$this->getMemberController();

        if ($oMemberController && $oMemberModel) {
            // if signed in, validate it.
            if ($oMemberModel->isLogged()) {
                $oMemberController->setSessionInfo();
            } elseif ($this->getGlobalCookie('xeak')) { // check auto sign-in
                $oMemberController->doAutologin();
            }

            $this->set('is_logged', $oMemberModel->isLogged());
            $this->set('logged_info', $oMemberModel->getLoggedInfo());
        }
    }

    /**
     * Starts session
     * Configures custom session handler in db, if enabled
     */
    public function startSession()
    {
        // set session handler
        if ($this->isInstalled() && $this->db_info->use_db_session == 'Y') {
            $oSessionModel = &$this->getSessionModel();
            $oSessionController = &$this->getSessionController();

            $this->setSessionSaveHandler(
                array(&$oSessionController, 'open'),
                array(&$oSessionController, 'close'),
                array(&$oSessionModel, 'read'),
                array(&$oSessionController, 'write'),
                array(&$oSessionController, 'destroy'),
                array(&$oSessionController, 'gc')
            );
        }

        $this->startPHPSession();
        if ($sess = $this->getPOSTArgument($this->getSessionName())) {
            $this->setSessionId($sess);
        }
    }

    public function getCurrentLanguage($enabled_languages, $default_language)
    {
        $current_language = null;
        // Retrieve language type set in user's cookie
        if ($this->get('l')) {
            $current_language = $this->get('l');
            if ($this->getGlobalCookie('lang_type') != $current_language) {
                $this->setCookie('lang_type', $current_language, time() + 3600 * 24 * 1000, '/');
            }
        } elseif ($this->getGlobalCookie('lang_type')) {
            $current_language = $this->getGlobalCookie('lang_type');
        }

        // If it's not exists, follow default language type set in db_info
        if (!$current_language) $current_language = $default_language;

        // if still lang_type has not been set or has not-supported type , set as English.
        if (!$current_language) $current_language = 'en';
        if (is_array($enabled_languages) && !isset($enabled_languages[$current_language])) {
            $current_language = 'en';
        }

        return $current_language;
    }

    public function initializeRequestArguments()
    {
        $this->_setXmlRpcArgument();
        $this->_setJSONRequestArgument();
        $this->_setRequestArgument();
        $this->_setUploadedArgument();
    }

    /**
     * Returns info about current site and its default module
     *
     * @param $default_url
     * @return mixed
     */
    public function getSiteModuleInfo()
    {
        $oModuleModel = &getModel('module');
        $site_module_info = $oModuleModel->getDefaultMid();
        return $site_module_info;
    }

    /**
     * default_url: $this->db_info->default_url
     *
     * @param $default_url
     * @return mixed
     */
    public function getCurrentSiteInfo($default_url)
    {
        $site_module_info = $this->getSiteModuleInfo();

        // if site_srl of site_module_info is 0 (default site), compare the domain to default_url of db_config
        if ($site_module_info->site_srl == 0 && $site_module_info->domain != $default_url) {
            $site_module_info->domain = $default_url;
        }

        return $site_module_info;
    }

    /**
     * set context variables in $GLOBALS (to use in display handler)
     *
     * @param $global_context
     * @param $global_lang
     * @param $global_cookie
     */
    public function linkContextToGlobals(&$global_lang, &$global_cookie)
    {
        $this->context->lang = &$global_lang;
        $this->context->_COOKIE = &$global_cookie;
    }

    /**
     * Finalize using resources, such as DB connection
     *
     * @return void
     */
    function close() {
        // Session Close
        if(function_exists('session_write_close')) session_write_close();

        // DB close
        $oDB = &DB::getInstance();
        if(is_object($oDB)&&method_exists($oDB, 'close')) $oDB->close();
    }

    function getGlobalAppSettings($custom_global_app_settings, $current_site_info)
    {
        $global_app_settings = clone($custom_global_app_settings);

        // If master_db information does not exist, the config file needs to be updated
        if(!isset($global_app_settings->master_db)) {
            $global_app_settings->master_db = array();
            $global_app_settings->master_db["db_type"] = $global_app_settings->db_type; unset($global_app_settings->db_type);
            $global_app_settings->master_db["db_port"] = $global_app_settings->db_port; unset($global_app_settings->db_port);
            $global_app_settings->master_db["db_hostname"] = $global_app_settings->db_hostname; unset($global_app_settings->db_hostname);
            $global_app_settings->master_db["db_password"] = $global_app_settings->db_password; unset($global_app_settings->db_password);
            $global_app_settings->master_db["db_database"] = $global_app_settings->db_database; unset($global_app_settings->db_database);
            $global_app_settings->master_db["db_userid"] = $global_app_settings->db_userid; unset($global_app_settings->db_userid);
            $global_app_settings->master_db["db_table_prefix"] = $global_app_settings->db_table_prefix; unset($global_app_settings->db_table_prefix);
            if(substr($global_app_settings->master_db["db_table_prefix"],-1)!='_') $global_app_settings->master_db["db_table_prefix"] .= '_';

            $slave_db = $global_app_settings->master_db;
            $global_app_settings->slave_db = array($slave_db);

            $this->setDBInfo($global_app_settings);

            $oInstallController = &$this->getInstallController();
            $oInstallController->makeConfigFile();
        }

        if(!$global_app_settings->use_prepared_statements) {
            $global_app_settings->use_prepared_statements = 'Y';
        }

        if(!$global_app_settings->time_zone) {
            $global_app_settings->time_zone = date('O');
        }

        if($global_app_settings->qmail_compatibility != 'Y') {
            $global_app_settings->qmail_compatibility = 'N';
        }

        if(!$global_app_settings->use_db_session) {
            $global_app_settings->use_db_session = 'N';
        }

        if(!$global_app_settings->use_ssl) {
            $global_app_settings->use_ssl = 'none';
        }

        $global_app_settings->lang_type = $current_site_info->default_language;
        if (!$global_app_settings->lang_type) {
            $global_app_settings->lang_type = 'en';
        }

        return $global_app_settings;
    }

    /**
     * Loads the global app configuration - from the db.config.php file;
     * Initializes other global app settings - like whether to use ssl or not and other
     * Loads current site information (including info about the module set as default)
     *
     * @return void
     */
    public function initializeAppSettingsAndCurrentSiteInfo() {
        if(!$this->isInstalled()) return;

        $custom_global_app_settings = $this->loadDbInfoFromConfigFile();
        // Set app configuration in db_info for getting current site info, otherwise it won't know db connection info
        $this->setDBInfo($custom_global_app_settings);

        $current_site_info = $this->getCurrentSiteInfo($custom_global_app_settings->default_url);

        $global_app_settings = $this->getGlobalAppSettings($custom_global_app_settings, $current_site_info);

        // Set $time_zone in $GLOBALS['_time_zone']
        $time_zone = &$this->getGlobals('_time_zone');
        $time_zone = $global_app_settings->time_zone;

        // Set $qmail_compatibility in $GLOBALS['_qmail_compatibility']
        $qmail_compatibility = &$this->getGlobals('_qmail_compatibility');
        $qmail_compatibility = $global_app_settings->qmail_compatibility;

        // Set some settings as variables in Context
        $this->set('_use_ssl', $global_app_settings->use_ssl);

        if($global_app_settings->http_port)  {
            $this->set('_http_port', $global_app_settings->http_port);
        }

        if($global_app_settings->https_port) {
            $this->set('_https_port', $global_app_settings->https_port);
        }

        // Set current site info in Context
        $this->set('site_module_info', $current_site_info);

        // Set vid, if this is a virtual site
        if ($current_site_info->site_srl && $this->isSiteID($current_site_info->domain)) {
            $this->set('vid', $current_site_info->domain, true);
        }

        // check if using rewrite module
        if(file_exists(_XE_PATH_.'.htaccess') && $global_app_settings->use_rewrite == 'Y') {
            $this->allow_rewrite = true;
        }
        else {
            $this->allow_rewrite = false;
        }

        $this->setDBInfo($global_app_settings);
    }

    /**
     * Get DB's db_type
     *
     * @return string DB's db_type
     */
    function getDBType() {
        return $this->db_info->master_db["db_type"];
    }

    /**
     * Set DB information
     *
     * @param object $db_info DB information
     * @return void
     */
    public function setDBInfo($db_info) {
        $this->db_info = $db_info;
    }

    /**
     * Get DB information
     *
     * @return object DB information
     */
    public function getDBInfo() {
        return $this->db_info;
    }

    /**
     * Return ssl status
     *
     * @return object SSL status (Optional - none|always|optional)
     */
    public function getSslStatus()
    {
        $dbInfo = $this->getDBInfo();
        return $dbInfo->use_ssl;
    }

    /**
     * Return default URL
     *
     * @return string Default URL
     */
    function getDefaultUrl() {
        $db_info = $this->getDBInfo();
        return $db_info->default_url;
    }

    /**
     * Find supported languages
     *
     * @return array Supported languages
     */
    function loadLangSupported() {
        if(!$this->lang_supported) {
            $langs = $this->file_handler->readFileAsArray(_XE_PATH_.'common/lang/lang.info');
            foreach($langs as $val) {
                list($lang_prefix, $lang_text) = explode(',',$val);
                $lang_text = trim($lang_text);
                $this->lang_supported[$lang_prefix] = $lang_text;
            }
        }
        return $this->lang_supported;
    }

    /**
     * Find selected languages to serve in the site
     *
     * @return array Selected languages
     */
    function loadLangSelected() {
        if(!$this->lang_selected) {
            $file_handler = $this->file_handler;

            $orig_lang_file = _XE_PATH_.'common/lang/lang.info';
            $selected_lang_file = _XE_PATH_.'files/config/lang_selected.info';
            if(!$file_handler->hasContent($selected_lang_file)) {
                $old_selected_lang_file = _XE_PATH_.'files/cache/lang_selected.info';
                $file_handler->moveFile($old_selected_lang_file, $selected_lang_file);
            }

            if(!$file_handler->hasContent($selected_lang_file)) {
                $buff = $file_handler->readFile($orig_lang_file);
                $file_handler->writeFile($selected_lang_file, $buff);
                $this->lang_selected = $this->loadLangSupported();
            } else {
                $langs = $file_handler->readFileAsArray($selected_lang_file);
                foreach($langs as $val) {
                    list($lang_prefix, $lang_text) = explode(',',$val);
                    $lang_text = trim($lang_text);
                    $this->lang_selected[$lang_prefix] = $lang_text;
                }
            }
        }
        return $this->lang_selected;
    }

    /**
     * Single Sign On (SSO)
     *
     * SSO will enable users to sign in just once for both default and virtual site.
     * You will need this only if you are using virtual sites.
     *
     * @return bool True : Module handling is necessary in the control path of current request , False : Otherwise
     */
    function checkSSO() {
        // pass if it's not GET request or XE is not yet installed
        if($this->db_info->use_sso != 'Y'
            || $this->isCrawler()) {
            return true;
        }
        $checkActList = array('rss'=>1, 'atom'=>1);
        if($this->getRequestMethod() != 'GET'
            || !$this->isInstalled()
            || isset($checkActList[$this->get('act')])) {
            return true;
        }

        // pass if default URL is not set
        $default_url = trim($this->db_info->default_url);
        if(!$default_url) return true;
        if(substr($default_url,-1)!='/') {
            $default_url .= '/';
        }

        // for sites recieving SSO valdiation
        if($default_url == $this->getRequestUri()) {
            if($this->get('default_url')) {
                $url = base64_decode($this->get('default_url'));
                $url_info = parse_url($url);
                $query_string = isset($url_info['query']) ? $url_info['query'] .'&':'';
                $query_string.= $query_string.'SSOID='.$this->getSessionId();
                $url_info['query'] = $query_string;
                $redirect_url = sprintf('%s://%s%s%s?%s',$url_info['scheme'],$url_info['host'],isset($url_info['port'])?':'.$url_info['port']:'',$url_info['path'], $url_info['query']);
                return new RedirectResponse($redirect_url);
            }
            // for sites requesting SSO validation
        } else {
            // result handling : set session_name()
            if($this->get('SSOID')) {
                $session_name = $this->get('SSOID');
                $this->setCookie($this->getSessionName(), $session_name);
                $url = preg_replace('/([\?\&])$/','',str_replace('SSOID='.$session_name,'',$this->getRequestUrl()));
                return new RedirectResponse($url);
                // send SSO request
            } else if($this->getGlobalCookie('sso') != md5($this->getRequestUri()) && !$this->get('SSOID')) {
                $this->setCookie('sso', md5($this->getRequestUri()), 0 ,'/');
                $url = sprintf("%s?default_url=%s", $default_url, base64_encode($this->getRequestUrl()));
                return new RedirectResponse($url);
            }
        }

        return true;
    }

    /**
     * Check if FTP info is registered
     *
     * @return bool True: FTP information is registered, False: otherwise
     */
    function isFTPRegisted() {
        $ftp_config_file = $this->getFTPConfigFile();
        if(file_exists($ftp_config_file)) return true;
        return false;
    }

    /**
     * Get FTP information
     *
     * @return object FTP information
     */
    function getFTPInfo() {
        if(!$this->isFTPRegisted()) return null;

        $ftp_config_file = $this->getFTPConfigFile();
        include($ftp_config_file);

        return $ftp_info;
    }

    /**
     * Add string to browser title
     *
     * @param string $site_title Browser title to be added
     * @return void
     */
    function addBrowserTitle($site_title) {
        if(!$site_title) return;

        if($this->site_title) {
            $this->site_title .= ' - '.$site_title;
        } else {
            $this->site_title = $site_title;
        }
    }

    /**
     * Set string to browser title
     *
     * @param string $site_title Browser title  to be set
     * @return void
     */
    function setBrowserTitle($site_title) {
        if(!$site_title) return;
        $this->site_title = $site_title;
    }

    /**
     * Get browser title
     *
     * @return string Browser title(htmlspecialchars applied)
     */
    function getBrowserTitle() {
        $oModuleController = $this->getModuleController();
        $oModuleController->replaceDefinedLangCode($this->site_title);

        return htmlspecialchars($this->site_title);
    }

    /**
     * Load language file according to language type
     *
     * @param string $path Path of the language file
     * @return void
     */
    public function loadLang($path) {
        if(!$this->lang_type) {
            return;
        }

        $filename = $this->_loadXmlLang($path);
        if(!$filename) {
            $filename = $this->_loadPhpLang($path);
        }

        if(!is_array($this->loaded_lang_files)) {
            $this->loaded_lang_files = array();
        }

        if(in_array($filename, $this->loaded_lang_files)) {
            return;
        }

        if ($filename && $this->is_readable($filename)){
            $this->loaded_lang_files[] = $filename;
            $this->includeLanguageFile($filename);
        }else{
            $this->_evalxmlLang($path);
        }
    }

    /**
     * Includes a PHP language file (old php formar or compiled new XML format)
     *
     * @param $filename
     */
    public function includeLanguageFile($filename)
    {
        global $lang;

        if(!is_object($lang)) {
            $lang = new stdClass;
        }

        include($filename);
    }

    /**
     * Evaluates a compiled XML language file
     *
     * @param $content
     */
    public function evaluateLanguageFileContent($content)
    {
        global $lang;

        if(!is_object($lang)) {
            $lang = new stdClass;
        }

        eval($content);
    }

    /**
     * Wrapper for PHP's is_readable function so it can be mocked in tests
     *
     * @param $filename
     * @return bool
     */
    public function is_readable($filename)
    {
        return is_readable($filename);
    }

    /**
     * Evaluation of xml language file
     *
     * @param string Path of the language file
     * @return void
     */
    function _evalxmlLang($path) {
        $_path = 'eval://'.$path;

        if(in_array($_path, $this->loaded_lang_files)) {
            return;
        }

        if(substr($path,-1)!='/') $path .= '/';
        $file = $path.'lang.xml';

        $oXmlLangParser = $this->getXmlLangParser($file, $this->lang_type);
        $content = $oXmlLangParser->getCompileContent();

        if ($content){
            $this->loaded_lang_files[] = $_path;
            $this->evaluateLanguageFileContent($content);
        }
    }

    /**
     * Load language file of xml type
     *
     * @param string $path Path of the language file
     * @return string file name
     */
    function _loadXmlLang($path) {
        if(substr($path,-1)!='/') $path .= '/';
        $xml_file_name = $path.'lang.xml';

        $oXmlLangParser = $this->getXmlLangParser($xml_file_name, $this->lang_type);
        $compiled_file_name = $oXmlLangParser->compile();

        return $compiled_file_name;
    }

    /**
     * Returns an instnace of the XmlLangParse class
     * Used for tests
     *
     * @param $file
     * @param $lang_type
     * @return XmlLangParser
     */
    public function getXmlLangParser($file, $lang_type)
    {
        return new XmlLangParser($file, $lang_type);
    }

    /**
     * Load language file of php type
     *
     * @param string $path Path of the language file
     * @return string file name
     */
    function _loadPhpLang($path) {
        if(substr($path,-1)!='/') $path .= '/';
        $path_tpl = $path.'%s.lang.php';
        $file = sprintf($path_tpl, $this->lang_type);

        $langs = array('ko','en'); // this will be configurable.
        while(!$this->is_readable($file) && $langs[0]) {
            $file = sprintf($path_tpl, array_shift($langs));
        }

        if(!$this->is_readable($file)) return false;
        return $file;
    }

    /**
     * Set lang_type
     *
     * @param string $lang_type Language type.
     * @return void
     */
    function setLangType($lang_type = 'ko') {
        $this->lang_type = $lang_type;
        $this->set('lang_type', $lang_type);

        $_SESSION['lang_type'] = $lang_type;
    }

    /**
     * Get lang_type
     *
     * @return string Language type
     */
    function getLangType() {
        return $this->lang_type;
    }

    /**
     * Return string accoring to the inputed code
     *
     * @param string $code Language variable name
     * @return string If string for the code exists returns it, otherwise returns original code
     */
    function getLang($code) {
        if(!$code) return;
        if($GLOBALS['lang']->{$code}) return $GLOBALS['lang']->{$code};
        return $code;
    }

    /**
     * Set data to lang variable
     *
     * @param string $code Language variable name
     * @param string $val `$code`s value
     * @return void
     */
    function setLang($code, $val) {
        $GLOBALS['lang']->{$code} = $val;
    }

    /**
     * Convert strings of variables in $source_object into UTF-8
     *
     * @param object $source_obj Conatins strings to convert
     * @return object converted object
     */
    function convertEncoding($source_obj) {
        $charset_list = array(
            'UTF-8', 'EUC-KR', 'CP949', 'ISO8859-1', 'EUC-JP', 'SHIFT_JIS', 'CP932',
            'EUC-CN', 'HZ', 'GBK', 'GB18030', 'EUC-TW', 'BIG5', 'CP950', 'BIG5-HKSCS',
            'ISO2022-CN', 'ISO2022-CN-EXT', 'ISO2022-JP', 'ISO2022-JP-2', 'ISO2022-JP-1',
            'ISO8859-6', 'ISO8859-8', 'JOHAB', 'ISO2022-KR', 'CP1255', 'CP1256', 'CP862',
            'ASCII', 'ISO8859-1', 'ISO8850-2', 'ISO8850-3', 'ISO8850-4', 'ISO8850-5',
            'ISO8850-7', 'ISO8850-9', 'ISO8850-10', 'ISO8850-13', 'ISO8850-14',
            'ISO8850-15', 'ISO8850-16', 'CP1250', 'CP1251', 'CP1252', 'CP1253', 'CP1254',
            'CP1257', 'CP850', 'CP866',
        );

        $obj = clone($source_obj);

        foreach($charset_list as $charset)
        {
            array_walk($obj,array($this, 'checkConvertFlag') ,$charset);
            $flag = true;
            $flag = $this->checkConvertFlag($flag);
            if($flag)
            {
                if($charset == 'UTF-8') {
                    return $obj;
                }
                array_walk($obj, array($this, 'doConvertEncoding'),$charset);
                return $obj;
            }
        }
        return $obj;
    }
    /**
     * Check flag
     *
     * @param mixed $val
     * @param string $key
     * @param mixed $charset charset
     * @return void
     */
    function checkConvertFlag(&$val, $key = null, $charset = null)
    {
        static $flag = true;
        if($charset)
        {
            if(is_array($val)) {
                array_walk($val,array($this, 'checkConvertFlag'),$charset);
            }
            else if($val && @iconv($charset, $charset, $val) != $val) {
                $flag = false;
            }
        }
        else
        {
            $return = $flag;
            $flag = true;
            return $return;
        }
    }

    /**
     * Convert array type variables into UTF-8
     *
     * @param mixed $val
     * @param string $key
     * @param string $charset character set
     * @see arrayConvWalkCallback will replaced array_walk_recursive in >=PHP5
     * @return object converted object
     */
    function doConvertEncoding(&$val, $key = null, $charset)
    {
        if (is_array($val))
        {
            array_walk($val,array($this, 'doConvertEncoding'),$charset);
        }
        else $val = iconv($charset, 'UTF-8', $val);
    }

    /**
     * Convert strings into UTF-8
     *
     * @param string $str String to convert
     * @return string converted string
     */
    function convertEncodingStr($str) {
        $obj = new stdClass();
        $obj->str = $str;
        $obj = $this->convertEncoding($obj);
        return $obj->str;
    }

    /**
     * Force to set response method
     *
     * @param string $method Response method. [HTML|XMLRPC|JSON]
     * @return void
     */
    public function setResponseMethod($method='HTML') {
        $methods = array('HTML'=>1, 'XMLRPC'=>1, 'JSON'=>1);
        $this->response_method = isset($methods[$method]) ? $method : 'HTML';
    }

    /**
     * Get reponse method
     *
     * @return string Response method. If it's not set, returns request method.
     */
    public function getResponseMethod() {
        if($this->response_method) return $this->response_method;

        $method  = $this->getRequestMethod();
        $methods = array('HTML'=>1, 'XMLRPC'=>1, 'JSON'=>1);

        return isset($methods[$method]) ? $method : 'HTML';
    }

    /**
     * Determine request method
     *
     * @param string $type Request method. (Optional - GET|POST|XMLRPC|JSON)
     * @return void
     */
    public function setRequestMethod($type) {
        if($type) {
            $this->request_method = $type;
        }
    }

    /**
     * handle request areguments for GET/POST
     *
     * @return void
     */
    function _setRequestArgument() {
        if(!count($this->getRequest())) return;

        foreach($this->getRequest() as $key => $val) {
            if($val === '' || $this->get($key)) continue;
            $val = $this->_filterRequestVar($key, $val);

            $get_arguments = $this->getArgumentsForGETRequest();
            $post_arguments = $this->getArgumentsForPOSTRequest();

            if($this->getRequestMethod()=='GET'&&isset($get_arguments[$key])) $set_to_vars = true;
            elseif($this->getRequestMethod()=='POST'&&isset($post_arguments[$key])) $set_to_vars = true;
            else $set_to_vars = false;

            if($set_to_vars)
            {
                $this->_recursiveCheckVar($val);
            }

            $this->set($key, $val, $set_to_vars);
        }
    }

    /**
     * Tests that the string does not contain php script tags
     * @See http://php.net/manual/ro/language.basic-syntax.phpmode.php
     *
     * 1. <?php ... ?>
     * 2. <script language="php"> ... </script>
     * 3. <? ... ?>
     * 4. <% ... %>
     *
     * @param $val
     */
    function _recursiveCheckVar($val)
    {
        if(is_string($val))
        {
            foreach($this->patterns as $pattern)
            {
                $result = preg_match($pattern, $val);
                if($result)
                {
                    // TODO This triggers an Invalid request in ModuleHandler constructor
                    $this->isSuccessInit = false;
                    return;
                }
            }
        }
        else if(is_array($val))
        {
            foreach($val as $val2)
            {
                $this->_recursiveCheckVar($val2);
            }
        }
    }

    /**
     * Handle request arguments for JSON
     *
     * @return void
     */
    function _setJSONRequestArgument() {
        if($this->getRequestMethod() != 'JSON')
            return;

        $params = array();
        parse_str($this->getRequestContent(),$params);

        foreach($params as $key => $val) {
            $val = $this->_filterRequestVar($key, $val,0);
            $this->set($key, $val, true);
        }
    }

    /**
     * Handle request arguments for XML RPC
     *
     * @return void
     */
    function _setXmlRpcArgument(XmlParser $parser = null) {
        if($this->getRequestMethod() != 'XMLRPC')
            return;

        if($parser == null)
            $parser = new XmlParser();

        $xml_obj = $parser->parse();

        $params = $xml_obj->methodcall->params;
        unset($params->node_name);

        unset($params->attrs);
        if(!count($params)) return;
        foreach($params as $key => $obj) {
            $val = $this->_filterRequestVar($key, $obj->body,0);
            $this->set($key, $val, true);
        }
    }

    /**
     * Filter request variable
     *
     * @see Cast variables, such as _srl, page, and cpage, into interger
     * @param string $key Variable key
     * @param string $val Variable value
     * @param string $do_stripslashes Whether to strip slashes
     * @return mixed filtered value. Type are string or array
     */
    function _filterRequestVar($key, $val, $do_stripslashes = 1) {
        $isArray = true;
        if(!is_array($val))
        {
            $isArray = false;
            $val = array($val);
        }

        foreach($val as $k => $v)
        {
            if($key === 'page' || $key === 'cpage' || substr($key, -3) === 'srl')
            {
                if(!preg_match('/^[0-9,]+$/', $v))
                {
                    $val[$k] =  (int)$v;
                }
            }
            elseif($key === 'mid' || $key === 'vid' || $key === 'search_keyword')
            {
                $val[$k] = htmlspecialchars($v);
            }
            else
            {
                if($do_stripslashes
                    && $this->magicQuotesAreSupportedInCurrentPHPVersion()
                    && $this->magicQuotesAreOn()
                )
                {
                    $v = stripslashes($v);
                }

                if (is_string($v)) {
                    $val[$k] = trim($v);
                }
            }
        }

        if($isArray)
        {
            return $val;
        }
        else
        {
            return $val[0];
        }
    }

    public function magicQuotesAreOn()
    {
        return get_magic_quotes_gpc();
    }

    public function magicQuotesAreSupportedInCurrentPHPVersion()
    {
        return version_compare(PHP_VERSION, '5.9.0', '<');
    }

    /**
     * Check if there exists uploaded file
     *
     * @return bool True: exists, False: otherwise
     */
    function isUploaded() {
        return $this->is_uploaded;
    }

    /**
     * Handle uploaded file
     *
     * @return void
     */
    function _setUploadedArgument() {
        if($this->getRequestMethod() != 'POST') return;
        if(!preg_match('/multipart\/form-data/i',$this->getRequestContentType())) return;
        if(!$this->getFiles()) return;

        foreach($this->getFiles() as $key => $val) {
            $tmp_name = $val['tmp_name'];
            if(!is_array($tmp_name)){
                if(!$tmp_name || !$this->is_uploaded_file($tmp_name)) continue;
                $val['name'] = htmlspecialchars($val['name']);
                $this->set($key, $val, true);
                $this->is_uploaded = true;
            }else {
                $files = array();
                for($i=0;$i< count($tmp_name);$i++){
                    $tmp_name = $val['tmp_name'][$i];
                    if(!$tmp_name || !$this->is_uploaded_file($tmp_name)) continue;
                    if($val['size'][$i] > 0) {
                        $file = array();
                        $file['name'] = $val['name'][$i];
                        $file['type'] = $val['type'][$i];
                        $file['tmp_name'] = $val['tmp_name'][$i];
                        $file['error'] = $val['error'][$i];
                        $file['size'] = $val['size'][$i];
                        $files[] = $file;
                    }
                }
                if(count($files) > 0)
                {
                    $this->set($key, $files, true);
                    $this->is_uploaded = true;
                }

            }
        }
    }

    /**
     * Return request method
     * @return string Request method type. (Optional - GET|POST|XMLRPC|JSON)
     */
    public function getRequestMethod() {
        if($this->request_method == "")
        {
            if(strpos($this->getRequestContentType(),'json'))
                $this->request_method = 'JSON';
            else if($this->getRequestContent())
                $this->request_method = 'XMLRPC';
            else if($this->getServerRequestMethod())
                $this->request_method = $this->getServerRequestMethod();
            else
                $this->request_method = 'GET';
        }

        return $this->request_method;
    }

    /**
     * Return request URL
     * @return string request URL
     */
    function getRequestUrl() {
        static $url = null;
        if(is_null($url)) {
            $url = $this->getRequestUri();
            if(count($this->getArgumentsForGETRequest()))
            {
                foreach($this->getArgumentsForGETRequest() as $key => $val)
                {
                    $vars[] = $key . '=' . ($val ? urlencode($this->convertEncodingStr($val)) : '');
                }
                $url .= '?' . join('&', $vars);
            }
        }
        return $url;
    }

    /**
     * Make URL with args_list upon request URL
     *
     * @param int $num_args Arguments nums
     * @param array $args_list Argument list for set url
     * @param string $domain Domain
     * @param bool $encode If true, use url encode.
     * @param bool $autoEncode If true, url encode automatically, detailed. Use this option, $encode value should be true
     * @return string URL
     */
    function getUrl($num_args=0, $args_list=array(), $domain = null, $encode = true, $autoEncode = false) {
        $vid = null;
        $domain = null;

        // retrieve virtual site information
        if(is_null($this->site_module_info)) {
            $this->site_module_info = $this->get('site_module_info');
        }

        // If $domain is set, handle it (if $domain is vid type, remove $domain and handle with $vid)
        if($domain && $this->isSiteID($domain)) {
            $vid = $domain;
            $domain = '';
        }

        // If $domain, $vid are not set, use current site information
        if(!$domain && !$vid) {
            if($this->site_module_info->domain && $this->isSiteID($this->site_module_info->domain)) {
                $vid = $this->site_module_info->domain;
            }
            else {
                $domain = $this->site_module_info->domain;
            }
        }

        // if $domain is set, compare current URL. If they are same, remove the domain, otherwise link to the domain.
        if($domain) {
            $domain_info = parse_url($domain);
            if(is_null($this->current_info)) {
                $this->current_info = parse_url(($this->getServerRequestHttps()=='on'?'https':'http').'://'.$this->getServerHost().$this->getScriptPath());
            }

            $domain_info_path = $domain_info['host']. (isset($domain_info['path']) ? $domain_info['path'] : '');
            $current_info_path = $this->current_info['host'].(isset($this->current_info['path']) ? $this->current_info['path'] : '');
            if($domain_info_path == $current_info_path) {
                unset($domain);
            } else {
                $domain = preg_replace('/^(http|https):\/\//i','', trim($domain));
                if(substr($domain,-1) != '/') {
                    $domain .= '/';
                }
            }
        }

        $get_vars = null;

        // If there is no GET variables or first argument is '' to reset variables
        if(!$this->get_vars || (count($args_list) && $args_list[0]=='')) {
            // rearrange args_list
            if(is_array($args_list) && count($args_list)  && $args_list[0]=='') {
                array_shift($args_list);
            }
        } else {
            // Otherwise, make GET variables into array
            $get_vars = get_object_vars($this->get_vars);
        }

        // arrange args_list
        for($i=0,$c=count($args_list);$i<$c;$i=$i+2) {
            $key = $args_list[$i];

            $temp_val = $args_list[$i+1];
            if(is_array($temp_val)) {
                $val = array();
                foreach($temp_val as $v) {
                    $val[] = trim($v);
                }
            } else {
                $val = trim($temp_val);
            }

            // If value is not set, remove the key
            if(!isset($val) || (!is_array($val) && !strlen($val))) {
                unset($get_vars[$key]);
                continue;
            }
            // set new variables
            $get_vars[$key] = $val;
        }

        // remove vid, rnd
        unset($get_vars['rnd']);
        if($vid) {
            $get_vars['vid'] = $vid;
        }
        else {
            unset($get_vars['vid']);
        }

        // organize URL
        $query = '';
        if(count($get_vars)) {
            // if using rewrite mod
            if($this->allow_rewrite) {
                $var_keys = array_keys($get_vars);
                sort($var_keys);

                $target = implode('.', $var_keys);

                $act = isset($get_vars['act']) ? $get_vars['act'] : '';
                $vid = isset($get_vars['vid']) ? $get_vars['vid'] : '';
                $mid = isset($get_vars['mid']) ? $get_vars['mid'] : '';
                $key = isset($get_vars['key']) ? $get_vars['key'] : '';
                $srl = isset($get_vars['document_srl']) ? $get_vars['document_srl'] : '';
                $entry = isset($get_vars['entry']) ? $get_vars['entry'] : '';

                $tmpArray = array('rss'=>1, 'atom'=>1, 'api'=>1);
                $is_feed = isset($tmpArray[$act]);

                $target_map = array(
                    'vid'=>$vid,
                    'mid'=>$mid,
                    'mid.vid'=>"$vid/$mid",

                    'entry.mid'    =>"$mid/entry/$entry",
                    'entry.mid.vid'=>"$vid/$mid/entry/$entry",

                    'document_srl'=>$srl,
                    'document_srl.mid'=>"$mid/$srl",
                    'document_srl.vid'=>"$vid/$srl",
                    'document_srl.mid.vid'=>"$vid/$mid/$srl",

                    'act.mid'    =>$is_feed?"$mid/$act":'',
                    'act.mid.vid'=>$is_feed?"$vid/$mid/$act":'',
                    'act.document_srl.key'    =>($act=='trackback')?"$srl/$key/$act":'',
                    'act.document_srl.key.mid'=>($act=='trackback')?"$mid/$srl/$key/$act":'',
                    'act.document_srl.key.vid'=>($act=='trackback')?"$vid/$srl/$key/$act":'',
                    'act.document_srl.key.mid.vid'=>($act=='trackback')?"$vid/$mid/$srl/$key/$act":''
                );

                $query = isset($target_map[$target]) ? $target_map[$target] : '';
            }

            if(!$query) {
                $queries = array();
                foreach($get_vars as $key => $val) {
                    if(is_array($val) && count($val)) {
                        foreach($val as $k => $v) $queries[] = $key.'['.$k.']='.urlencode($v);
                    } else {
                        $queries[] = $key.'='.@urlencode($val);
                    }
                }
                if(count($queries)) {
                    $query = 'index.php?'.implode('&', $queries);
                }
            }
        }

        // If using SSL always
        $_use_ssl = $this->get('_use_ssl');
        if($_use_ssl == 'always') {
            $query = $this->getRequestUri(ENFORCE_SSL, $domain).$query;
            // optional SSL use
        } elseif($_use_ssl == 'optional') {
            $ssl_mode = RELEASE_SSL;
            if($get_vars['act'] && $this->isExistsSSLAction($get_vars['act'])) {
                $ssl_mode = ENFORCE_SSL;
            }
            $query = $this->getRequestUri($ssl_mode, $domain).$query;
            // no SSL
        } else {
            // currently on SSL but target is not based on SSL
            if($this->getServerRequestHttps()=='on' ) {
                $query = $this->getRequestUri(ENFORCE_SSL, $domain).$query;
            }

            // if $domain is set
            else if($domain) {
                $query = $this->getRequestUri(FOLLOW_REQUEST_SSL, $domain).$query;
            }

            else {
                $query = $this->getScriptPath().$query;
            }
        }

        if ($encode) {
            if($autoEncode) {
                $parsedUrl = parse_url($query);
                parse_str($parsedUrl['query'], $output);
                $encode_queries = array();
                foreach($output as $key=>$value){
                    if (preg_match('/&([a-z]{2,}|#\d+);/', urldecode($value))){
                        $value = urlencode(htmlspecialchars_decode(urldecode($value)));
                    }
                    $encode_queries[] = $key.'='.$value;
                }
                $encode_query = implode('&', $encode_queries);
                return htmlspecialchars($parsedUrl['path'].'?'.$encode_query);
            }
            else {
                return htmlspecialchars($query);
            }
        } else {
            return $query;
        }
    }

    /**
     * Return after removing an argument on the requested URL
     *
     * @param string $ssl_mode SSL mode
     * @param string $domain Domain
     * @retrun string converted URL
     */
    function getRequestUri($ssl_mode = FOLLOW_REQUEST_SSL, $domain = null) {
        // Make sure this is a valid HTTP Request
        $request_protocol = $this->getServerRequestProtocol();
        if(!isset($request_protocol)) {
            return ;
        }
        if($this->get('_use_ssl') == 'always') {
            $ssl_mode = ENFORCE_SSL;
        }

        if($domain) {
            $domain_key = md5($domain);
        }
        else {
            $domain_key = 'default';
        }

        if(isset($this->url[$ssl_mode][$domain_key])) {
            return $this->url[$ssl_mode][$domain_key];
        }

        $current_use_ssl = $this->getServerRequestHttps() =='on' ? true : false;

        switch($ssl_mode) {
            case FOLLOW_REQUEST_SSL: $use_ssl = $current_use_ssl; break;
            case ENFORCE_SSL: $use_ssl = true;  break;
            case RELEASE_SSL: $use_ssl = false; break;
        }

        if($domain) {
            $target_url = trim($domain);
            if(substr($target_url,-1) != '/') $target_url.= '/';
        } else {
            $target_url= $this->getServerHost() . $this->getScriptPath();
        }

        $url_info = parse_url('http://'.$target_url);

        if($current_use_ssl != $use_ssl)
        {
            unset($url_info['port']);
        }

        if($use_ssl) {
            $port = $this->get('_https_port');
            if($port && $port != 443)      {
                $url_info['port'] = $port;
            }
            elseif(isset($url_info['port']) && $url_info['port']==443) {
                unset($url_info['port']);
            }
        } else {
            $port = $this->get('_http_port');
            if($port && $port != 80)      {
                $url_info['port'] = $port;
            }
            elseif(isset($url_info['port']) && $url_info['port']==80) {
                unset($url_info['port']);
            }
        }

        $this->url[$ssl_mode][$domain_key] = sprintf('%s://%s%s%s',$use_ssl?'https':$url_info['scheme'], $url_info['host'], isset($url_info['port'])&&$url_info['port']!=80?':'.$url_info['port']:'',$url_info['path']);

        return $this->url[$ssl_mode][$domain_key];
    }

    /**
     * Set a context value with a key
     *
     * @param string $key Key
     * @param string $val Value
     * @param mixed $set_to_get_vars If not false, Set to get vars.
     * @return void
     */
    public function set($key, $val, $set_to_get_vars=0) {
        if(!$this->context) $this->context = new stdClass();
        $this->context->{$key} = $val;
        if($set_to_get_vars === false) return;
        if($val === null || $val === '')
        {
            unset($this->get_vars->{$key});
            return;
        }
        if($set_to_get_vars ||
            ($this->get_vars && property_exists($this->get_vars, $key) && $this->get_vars->{$key})) {
            if(!$this->get_vars) $this->get_vars = new stdClass();
            $this->get_vars->{$key} = $val;
        }
    }

    /**
     * Return key's value
     *
     * @param string $key Key
     * @return string Key
     */
    public function get($key) {
        if(!isset($this->context->{$key})) return null;
        return $this->context->{$key};
    }

    /**
     * Get one more vars in object vars with given arguments(key1, key2, key3,...)
     *
     * @return object
     */
    function gets() {
        $num_args = func_num_args();
        if($num_args<1) return;

        $args_list = func_get_args();
        $output = new stdClass();
        foreach($args_list as $v) {
            $output->{$v} = $this->get($v);
        }
        return $output;
    }

    /**
     * Return all data
     *
     * @return object All data
     */
    public function getAll() {
        return $this->context;
    }

    /**
     * Return values from the GET/POST/XMLRPC
     *
     * @return Object Request variables.
     */
    public function getRequestVars() {
        if($this->get_vars) return clone($this->get_vars);
        return new stdClass;
    }

    function sslActionsFileExists()
    {
        return is_readable($this->file_handler->getRealPath($this->sslActionCacheFile));
    }

    function createSslActionsFile()
    {
        $buff = '<?php if(!defined("__XE__"))exit;';
        FileHandler::writeFile($this->file_handler->getRealPath($this->sslActionCacheFile), $buff);
    }

    function enableSslAction($action)
    {
        $sslActionCacheString = sprintf('$sslActions[\'%s\'] = 1;', $action);
        FileHandler::writeFile($this->file_handler->getRealPath($this->sslActionCacheFile), $sslActionCacheString, 'a');
    }

    /**
     * Register if actions is to be encrypted by SSL. Those actions are sent to https in common/js/xml_handler.js
     *
     * @param string $action act name
     * @return void
     */
    function addSSLAction($action)
    {
        if(!$this->sslActionsFileExists()) {
            $this->createSslActionsFile();
        }

        if(!isset($this->ssl_actions[$action])) {
            $this->enableSslAction($action);
        }
    }

    /**
     * Get SSL Action
     *
     * @return string act
     */
    public function getSSLActions() {
        return $this->ssl_actions;
    }

    /**
     * Check SSL action are existed
     *
     * @param string $action act name
     * @return bool If SSL exists, return true.
     */
    public function isExistsSSLAction($action) {
        return isset($this->ssl_actions[$action]);
    }

    /**
     * Load front end file
     *
     * @param array $args array
     * case js :
     *		$args[0]: file name,
     *		$args[1]: type (head | body),
     *		$args[2]: target IE,
     *		$args[3]: index
     * case css :
     *		$args[0]: file name,
     *		$args[1]: media,
     *		$args[2]: target IE,
     *		$args[3]: index
     * @param bool $useCdn use cdn
     * @param string $cdnPrefix cdn prefix
     * @param string $cdnVersion cdn version
     *
     */
    function loadFile($args, $useCdn = false, $cdnPrefix = '', $cdnVersion = '')
    {
        if ($useCdn && !$cdnPrefix)
        {
            $cdnPrefix = __XE_CDN_PREFIX__;
            $cdnVersion = __XE_CDN_VERSION__;
        }

        $this->oFrontEndFileHandler->loadFile($args, $useCdn, $cdnPrefix, $cdnVersion);
    }

    /**
     * Unload front end file
     *
     * @param string $file File name with path
     * @param string $targetIe Target IE
     * @param string $media Media query
     * @return void
     */
    function unloadFile($file, $targetIe = '', $media = 'all')
    {
        $this->oFrontEndFileHandler->unloadFile($file, $targetIe, $media);
    }

    /**
     * Unload front end file all
     *
     * @param string $type Unload target (optional - all|css|js)
     * @return void
     */

    function unloadAllFiles($type = 'all')
    {
        $this->oFrontEndFileHandler->unloadAllFiles($type);
    }

    /**
     * Add the js file
     *
     * @deprecated
     * @param string $file File name with path
     * @param string $optimized optimized (That seems to not use)
     * @param string $targetie target IE
     * @param string $index index
     * @param string $type Added position. (head:<head>..</head>, body:<body>..</body>)
     * @param bool $isRuleset Use ruleset
     * @param string $autoPath If path not readed, set the path automatically.
     * @return void
     */
    function addJsFile($file, $optimized = false, $targetie = '',$index=0, $type='head', $isRuleset = false, $autoPath = null) {
        if($isRuleset)
        {
            if (strpos($file, '#') !== false){
                $file = str_replace('#', '', $file);
                if (!is_readable($file)) $file = $autoPath;
            }
            // TODO I think Validator needs some refactoring itself
            $validator = $this->validator;
            $validator->setRulesetPath($file);
            $validator->setCacheDir('files/cache');
            $file = $validator->getJsPath();
        }

        $this->oFrontEndFileHandler->loadFile(array($file, $type, $targetie, $index));
    }

    /**
     * Remove the js file
     *
     * @deprecated
     * @param string $file File name with path
     * @param string $optimized optimized (That seems to not use)
     * @param string $targetie target IE
     * @return void
     */
    function unloadJsFile($file, $optimized = false, $targetie = '') {
        $this->oFrontEndFileHandler->unloadFile($file, $targetie);
    }

    /**
     * Unload all javascript files
     *
     * @return void
     */
    function unloadAllJsFiles() {
        $this->oFrontEndFileHandler->unloadAllFiles('js');
    }

    /**
     * Add javascript filter
     *
     * @param string $path File path
     * @param string $filename File name
     * @return void
     */
    function addJsFilter($path, $filename) {
        $oXmlFilter = new XmlJsFilter($path, $filename);
        $oXmlFilter->compile();
    }

    /**
     * Returns the list of javascripts that matches the given type.
     *
     * @param string $type Added position. (head:<head>..</head>, body:<body>..</body>)
     * @return array Returns javascript file list. Array contains file, targetie.
     */
    function getJsFile($type='head') {
        return $this->oFrontEndFileHandler->getJsFileList($type);
    }

    /**
     * Add CSS file
     *
     * @deprecated
     * @param string $file File name with path
     * @param string $optimized optimized (That seems to not use)
     * @param string $media Media query
     * @param string $targetie target IE
     * @param string $index index
     * @return void
     *
     */
    function addCSSFile($file, $optimized=false, $media='all', $targetie='',$index=0) {
        $this->oFrontEndFileHandler->loadFile(array($file, $media, $targetie, $index));
    }

    /**
     * Remove css file
     *
     * @deprecated
     * @param string $file File name with path
     * @param string $optimized optimized (That seems to not use)
     * @param string $media Media query
     * @param string $targetie target IE
     * @return void
     */
    function unloadCSSFile($file, $optimized = false, $media = 'all', $targetie = '') {
        $this->oFrontEndFileHandler->unloadFile($file, $targetie, $media);
    }

    /**
     * Unload all css files
     *
     * @return void
     */
    function unloadAllCSSFiles() {
        $this->oFrontEndFileHandler->unloadAllFiles('css');
    }

    /**
     * Return a list of css files
     *
     * @return array Returns css file list. Array contains file, media, targetie.
     */
    function getCSSFile() {
        return $this->oFrontEndFileHandler->getCssFileList();
    }

    /**
     * Load javascript plugin
     *
     * @param string $plugin_name plugin name
     * @return void
     */
    function loadJavascriptPlugin($plugin_name) {
        if($plugin_name == 'ui.datepicker') $plugin_name = 'ui';

        if($this->loaded_javascript_plugins[$plugin_name]) {
            return;
        }
        $this->loaded_javascript_plugins[$plugin_name] = true;

        $plugin_path = './common/js/plugins/'.$plugin_name.'/';
        $info_file   = $plugin_path.'plugin.load';

        if(!$this->pluginConfigFileExistsAndIsReadable($info_file)) {
            return;
        }

        $list = $this->file_handler->readFileAsArray($info_file);
        foreach($list as $filename) {
            $filename = trim($filename);
            if(!$filename) {
                continue;
            }

            if(substr($filename,0,2)=='./') {
                $filename = substr($filename,2);
            }

            if(preg_match('/\.js$/i',  $filename))     {
                $this->loadFile(array($plugin_path.$filename, 'body', '', 0), true);
            }
            elseif(preg_match('/\.css$/i', $filename)) {
                $this->loadFile(array($plugin_path.$filename, 'all', '', 0), true);
            }
        }

        if($this->pluginUsesLocalization($plugin_path)) {
            $this->loadLang($plugin_path.'lang');
        }
    }

    public function pluginUsesLocalization($plugin_path)
    {
        return is_dir($plugin_path . 'lang');
    }

    public function pluginConfigFileExistsAndIsReadable($info_file)
    {
        return is_readable($info_file);
    }

    /**
     * Add html code before </head>
     *
     * @param string $header add html code before </head>.
     * @return void
     */
    function addHtmlHeader($header) {
        $this->html_header .= "\n".$header;
    }

    /**
     * Returns added html code by addHtmlHeader()
     *
     * @return string Added html code before </head>
     */
    function getHtmlHeader() {
        return $this->html_header;
    }

    /**
     * Add css class to Html Body
     *
     * @param string $class_name class name
     */
    function addBodyClass($class_name) {
        $this->body_class[] = $class_name;
    }

    /**
     * Return css class to Html Body
     *
     * @return string Return class to html body
     */
    function getBodyClass() {
        $this->body_class = array_unique($this->body_class);

        return count($this->body_class)?sprintf(' class="%s"', implode(' ',$this->body_class)):'';
    }

    /**
     * Add html code after <body>
     *
     * @param string $header Add html code after <body>
     */
    function addBodyHeader($header) {
        $this->body_header .= "\n".$header;
    }

    /**
     * Returns added html code by addBodyHeader()
     *
     * @return string Added html co de after <body>
     */
    function getBodyHeader() {
        return $this->body_header;
    }

    /**
     * Add html code before </body>
     *
     * @param string $footer Add html code before </body>
     */
    function addHtmlFooter($footer) {
        $this->html_footer .= ($this->Htmlfooter?"\n":'').$footer;
    }

    /**
     * Returns added html code by addHtmlHeader()
     *
     * @return string Added html code before </body>
     */
    function getHtmlFooter() {
        return $this->html_footer;
    }

    /**
     * Get config file
     *
     * @retrun string The path of the config file that contains database settings
     */
    function getConfigFile() {
        return _XE_PATH_.'files/config/db.config.php';
    }

    /**
     * Get FTP config file
     *
     * @return string The path of the config file that contains FTP settings
     */
    function getFTPConfigFile() {
        return _XE_PATH_.'files/config/ftp.config.php';
    }

    /**
     * Checks whether XE is installed
     *
     * @return bool True if the config file exists, otherwise false.
     */
    function isInstalled() {
        return FileHandler::hasContent($this->getConfigFile());
    }

    /**
     * Check whether it is allowed to use rewrite mod
     *
     * @return bool True if it is allowed to use rewrite mod, otherwise false
     */
    function isAllowRewrite() {
        return $this->allow_rewrite;
    }

    /**
     * Converts a local path into an URL
     *
     * @param string $path URL path
     * @return string Converted path
     */
    function pathToUrl($path) {
        $xe   = _XE_PATH_;
        $path = strtr($path, "\\", "/");

        $base_url = preg_replace('@^https?://[^/]+/?@', '', $this->getRequestUri());

        $_xe   = explode('/', $xe);
        $_path = explode('/', $path);
        $_base = explode('/', $base_url);

        if(!$_base[count($_base)-1]) array_pop($_base);

        foreach($_xe as $idx => $dir) {
            if($_path[0] != $dir) break;
            array_shift($_path);
        }

        $idx = count($_xe) - $idx - 1;
        while($idx--) {
            if(count($_base)) {
                array_shift($_base);
            }
            else {
                array_unshift($_base, '..');
            }
        }

        if(count($_base)) {
            array_unshift($_path, implode('/', $_base));
        }

        $path = '/'.implode('/', $_path);
        if(substr($path,-1)!='/') $path .= '/';
        return $path;
    }

    /**
     * Get meta tag
     * @return array The list of meta tags
     */
    function getMetaTag() {
        if(!is_array($this->meta_tags)) $this->meta_tags = array();

        $ret = array();
        $map = &$this->meta_tags;

        foreach($map as $key=>$val) {
            list($name, $is_http_equiv) = explode("\t", $key);
            $ret[] = array('name'=>$name, 'is_http_equiv'=>$is_http_equiv, 'content' => $val);
        }

        return $ret;
    }

    /**
     * Add the meta tag
     *
     * @param string $name name of meta tag
     * @param string $content content of meta tag
     * @param mixed $is_http_equiv value of http_equiv
     * @return void
     */
    function addMetaTag($name, $content, $is_http_equiv = false) {
        $key = $name."\t".($is_http_equiv ? '1' : '0');
        $map = &$this->meta_tags;

        $map[$key] = $content;
    }

    public function getNotEncodedSiteUrl()
    {
        $num_args = func_num_args();
        $args_list = func_get_args();

        if(!$num_args) return $this->getRequestUri();

        $domain = array_shift($args_list);
        $num_args = count($args_list);

        return $this->getUrl($num_args, $args_list, $domain, false);
    }
}

/**
 * Context class will be replaced by ContextInstance class in the future
 *
 * The entire app should share the same instance of the context,
 * but that doesn't mean calls have to be static
 *
 *
 * @deprecated
 */
class Context
{
    /** @var ContextInstance */
    private static $context;

    public static function setRequestContext(ContextInstance $context)
    {
        self::$context = $context;
    }

    public static function &getInstance()
    {
        return self::$context;
    }

    public static function get($key)
    {
        return self::$context->get($key);
    }

    public static function set($key, $value, $set_to_get_vars = 0)
    {
        self::$context->set($key, $value, $set_to_get_vars);
    }

    public static function gets()
    {
        $args_list = func_get_args();
        return call_user_func_array(array(self::$context, 'gets'), $args_list);
    }

    public static function getAll()
    {
        return self::$context->getAll();
    }

    public static function addBodyClass($class_name)
    {
        self::$context->addBodyClass($class_name);
    }

    public static function getBodyClass()
    {
        return self::$context->getBodyClass();
    }

    public static function getRequestMethod()
    {
        return self::$context->getRequestMethod();
    }

    public function setRequestMethod($type)
    {
        self::$context->setRequestMethod($type);
    }

    public static function getRequestVars()
    {
        return self::$context->getRequestVars();
    }

    public static function getLang($code)
    {
        return self::$context->getLang($code);
    }

    public static function loadLang($path)
    {
        self::$context->loadLang($path);
    }

    public static function loadLangSelected()
    {
        return self::$context->loadLangSelected();
    }

    public static function loadLangSupported()
    {
        return self::$context->loadLangSupported();
    }

    public static function getFtpInfo()
    {
        return self::$context->getFTPInfo();
    }

    public static function getFTPConfigFile()
    {
        return self::$context->getFTPConfigFile();
    }

    public static function isFtpRegisted()
    {
        return self::$context->isFTPRegisted();
    }

    public static function getDefaultUrl()
    {
        return self::$context->getDefaultUrl();
    }

    public static function getRequestUrl()
    {
        return self::$context->getRequestUrl();
    }

    public static function getUrl()
    {
        $args_list = func_get_args();
        return call_user_func_array(array(self::$context, 'getUrl'), $args_list);
    }

    public static function getRequestUri()
    {
        return self::$context->getRequestUri();
    }

    public static function getDbInfo()
    {
        return self::$context->getDbInfo();
    }

    public static function getDbType()
    {
        return self::$context->getDbType();
    }

    public static function isInstalled()
    {
        return self::$context->isInstalled();
    }

    public static function convertEncodingStr($str)
    {
        return self::$context->convertEncodingStr($str);
    }

    public static function convertEncoding($object)
    {
        return self::$context->convertEncoding($object);
    }

    public static function close()
    {
        self::$context->close();
        self::$context = null;
    }

    public static function loadFile($args, $useCdn = false, $cdnPrefix = '', $cdnVersion = '')
    {
        self::$context->loadFile($args, $useCdn, $cdnPrefix, $cdnVersion);
    }

    public static function unloadFile($file, $targetIe = '', $media = 'all')
    {
        self::$context->unloadFile($file, $targetIe, $media);
    }

    public static function getBodyHeader()
    {
        return self::$context->getBodyHeader();
    }

    public static function getHtmlHeader()
    {
        return self::$context->getHtmlHeader();
    }

    public static function getHtmlFooter()
    {
        return self::$context->getHtmlFooter();
    }

    public static function addHtmlFooter($footer)
    {
        return self::$context->addHtmlFooter($footer);
    }

    public static function addHtmlHeader($header)
    {
        self::$context->addHtmlHeader($header);
    }

    public static function getLangType()
    {
        return self::$context->getLangType();
    }

    public static function setLangType($lang_type = 'ko')
    {
        self::$context->setLangType($lang_type);
    }

    public static function getResponseMethod()
    {
        return self::$context->getResponseMethod();
    }

    public static function getBrowserTitle()
    {
        return self::$context->getBrowserTitle();
    }

    public static function setBrowserTitle($title)
    {
        self::$context->setBrowserTitle($title);
    }

    public static function loadJavascriptPlugin($plugin_name)
    {
        self::$context->loadJavascriptPlugin($plugin_name);
    }

    public static function isAllowRewrite()
    {
        return self::$context->isAllowRewrite();
    }

    public static function getSslStatus()
    {
        return self::$context->getSslStatus();
    }

    public static function getSslActions()
    {
        return self::$context->getSSLActions();
    }

    public static function getCssFile()
    {
        return self::$context->getCSSFile();
    }

    public static function addCssFile($file, $optimized=false, $media='all', $targetie='',$index=0)
    {
        self::$context->addCSSFile($file,$optimized, $media, $targetie, $index);
    }

    public static function getJsFile($type='head')
    {
        return self::$context->getJsFile($type);
    }

    public static function addJsFile($file, $optimized = false, $targetie = '',$index=0, $type='head', $isRuleset = false, $autoPath = null)
    {
        self::$context->addJsFile($file, $optimized, $targetie, $index, $type, $isRuleset, $autoPath);
    }

    public static function addJsFilter($path, $filename)
    {
        self::$context->addJsFilter($path, $filename);
    }

    public static function pathToUrl($path)
    {
        return self::$context->pathToUrl($path);
    }

    public static function setDbInfo($db_info)
    {
        self::$context->setDBInfo($db_info);
    }

    public static function getConfigFile()
    {
        return self::$context->getConfigFile();
    }


}