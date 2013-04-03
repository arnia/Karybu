<?php
/**
 * set the include of the class file and other environment configurations
 *
 * @file   config/config.inc.php
 * @author NHN (developers@xpressengine.com)
 **/

@error_reporting(-1);

if(!defined('__ZBXE__')) exit();

/**
 * Display XE's full version
 * Even The file should be revised when releasing altough no change is made
 */
define('__XE_VERSION__', '1.5.4.1');

/**
 * @deprecated __ZBXE_VERSION__ will be removed. Use __XE_VERSION__ instead.
 */
define('__ZBXE_VERSION__', __XE_VERSION__);

/**
 * The base path to where you installed zbXE Wanted
 */
define('_XE_PATH_', str_replace('config/config.inc.php', '', str_replace('\\', '/', __FILE__)));


// Set can use other method instead cookie to store session id(for file upload)
ini_set('session.use_only_cookies', 0);


if(file_exists(_XE_PATH_.'config/package.inc.php')) {
    require _XE_PATH_.'config/package.inc.php';
} else {
    /**
     * Package type
     */
    define('_XE_PACKAGE_','XE');

    /**
     * Location
     */
    define('_XE_LOCATION_','en');

    /**
     * Location site
     */
    define('_XE_LOCATION_SITE_','http://www.xpressengine.org/');

    /**
     * Download server
     */
    define('_XE_DOWNLOAD_SERVER_','http://en.download.xpressengine.org/');
}

/*
 * user configuration files which override the default settings
 * save the following information into config/config.user.inc.php
 * <?php
 * define('__DEBUG_PROTECT__', 1);
 * define('__DEBUG_PROTECT_IP__', '127.0.0.1');
 * define('__PROXY_SERVER__', 'http://domain:port/path');
 * define('__XE_CDN_PREFIX__', 'http://yourCdnDomain.com/path/');
 * define('__XE_CDN_VERSION__', 'yourCdnVersion');
 */
if(file_exists(_XE_PATH_.'config/config.user.inc.php')) {
    require _XE_PATH_.'config/config.user.inc.php';
}

if(!defined('__DEBUG_PROTECT__'))
{
    /**
     * output comments of the firePHP console and browser
     *
     * <pre>
     * 0: No limit (not recommended)
     * 1: Allow only specified IP addresses
     * </pre>
     */
    define('__DEBUG_PROTECT__', 1);
}

if(!defined('__DEBUG_PROTECT_IP__'))
{
    /**
     * Set a ip address to allow debug
     */
    define('__DEBUG_PROTECT_IP__', '127.0.0.1');
}

if(!defined('__PROXY_SERVER__'))
{
    /**
     * __PROXY_SERVER__ has server information to request to the external through the target server
     * FileHandler:: getRemoteResource uses the constant
     */
    define('__PROXY_SERVER__', null);
}

if(!defined('__XE_CDN_PREFIX__'))
{
    /**
     * CDN prefix
     */
    define('__XE_CDN_PREFIX__', 'http://static.xpressengine.com/core/');
}

if(!defined('__XE_CDN_VERSION__'))
{
    /**
     * CDN version
     */
    define('__XE_CDN_VERSION__', '%__XE_CDN_VERSION__%');
}

// Set Timezone as server time
if(version_compare(PHP_VERSION, '5.3.0') >= 0)
{
    date_default_timezone_set(@date_default_timezone_get());
}

// Require a function-defined-file for simple use
require(_XE_PATH_.'config/func.inc.php');

if(__DEBUG__) define('__StartTime__', getMicroTime());

if(__DEBUG__) define('__ClassLoadStartTime__', getMicroTime());

/**
 * composer autoloader
 * http://getcomposer.org/doc/04-schema.md#autoload
 **/
require_once dirname(__FILE__) . '/../vendor/autoload.php';

$cmsAutoloader = new \Karybu\Autoloader\Autoloader();

if(__DEBUG__) $GLOBALS['__elapsed_class_load__'] = getMicroTime() - __ClassLoadStartTime__;
