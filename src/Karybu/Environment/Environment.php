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
     * @access public
     * @return array()
     */
    public static function getEnvironment($env = null)
    {
        if (count(self::$_environments) == 0) {
            $dev                = array();
            $dev['code']        = self::DEV_ENVIRONMENT;
            $dev['lang_key']    = 'dev';

            $prod               = array();
            $prod['code']       = self::PROD_ENVIRONMENT;
            $prod['lang_key']   = 'prod';

            self::$_environments[self::DEV_ENVIRONMENT] = $dev;
            self::$_environments[self::PROD_ENVIRONMENT]= $prod;
        }
        if (is_null($env)) {
            return self::$_environments;
        }
        if (isset(self::$_environments[$env])) {
            return self::$_environments[$env];
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
