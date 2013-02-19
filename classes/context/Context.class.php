<?php

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

    function getJsFile($type='head')
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


}