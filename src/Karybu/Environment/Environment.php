<?php
namespace Karybu\Environment;

use Symfony\Component\Config\Definition\Exception\Exception;

final class Environment
{
    const DEFAULT_ENVIRONMENT       = 'prod';
    private static $_environments   = array();

    /**
     * get environment details
     * @param string $code
     * @param bool $useDefault - if true and env does not exist returns default env
     * @param bool $silent - if true no exception is thrown
     * @access public
     * @return EnvironmentInterface
     */
    public static function getEnvironment($env = null, $useDefault = false, $silent = true)
    {
        if (count(self::$_environments) == 0) {
            $dev = new EnvironmentDevelopment();
            self::addEnvironment($dev);
            $prod = new EnvironmentProduction();
            self::addEnvironment($prod);
        }
        if (is_null($env)) {
            return self::$_environments;
        }
        if (isset(self::$_environments[$env])) {
            return self::$_environments[$env];
        }
        elseif($useDefault) {
            if (isset(self::$_environments[self::DEFAULT_ENVIRONMENT])) {
                return self::$_environments[self::DEFAULT_ENVIRONMENT];
            }
        }
        if ($silent){
            return new \Object();
        }
        throw new Exception("Environment {$env} not set");
    }

    /**
     * get the list of environments
     * @access public
     * @return array
     */
    public static function getEnvironments() {
        return self::getEnvironment(null);
    }

    /**
     * add environment
     * access public
     * @param EnvironmentInterface $environment
     * @return bool
     */
    public static function addEnvironment(EnvironmentAbstract $environment){
        self::$_environments[$environment->getCode()] = $environment;
        return true;
    }
}
