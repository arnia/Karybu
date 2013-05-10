<?php
namespace Karybu\Environment;

final class Environment
{
    const DEV_ENVIRONMENT           = 'dev';
    const PROD_ENVIRONMENT          = 'prod';
    const DEFAULT_ENVIRONMENT       = 'prod';
    private static $_environments   = array();

    /**
     * get environment details
     * @param string $code
     * @param bool $useDefault - if true and env does not exist returns default env
     * @access public
     * @return array()
     */
    public static function getEnvironment($env = null, $useDefault = false)
    {
        if (count(self::$_environments) == 0) {
            $dev                = array();
            $dev['code']        = self::DEV_ENVIRONMENT;
            $dev['lang_key']    = 'dev';
            $dev['dev_mode']    = true;

            $prod               = array();
            $prod['code']       = self::PROD_ENVIRONMENT;
            $prod['lang_key']   = 'prod';
            $prod['dev_mode']    = false;

            self::$_environments[self::DEV_ENVIRONMENT] = $dev;
            self::$_environments[self::PROD_ENVIRONMENT]= $prod;
        }
        if (is_null($env)) {
            return self::$_environments;
        }
        if (isset(self::$_environments[$env])) {
            return self::$_environments[$env];
        }
        elseif($useDefault){
            return self::$_environments[self::DEFAULT_ENVIRONMENT];
        }
        return array();
    }

    /**
     * get the list of environments
     * @access public
     * @return array
     */
    public static function getEnvironments() {
        return self::getEnvironment(null);
    }
}
