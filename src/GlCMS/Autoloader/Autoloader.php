<?php
// florin, 2/6/13, 5:19 PM
namespace GlCMS\Autoloader;

class Autoloader
{
    protected $modulesPath;

    /**
     * Constructor sets classes path
     *
     * When a class is not found, the spl_autoload_register
     * sends to the loader function below with $class set to the missing class name.
     */
    public function __construct($modulesPath=null)
    {
        spl_autoload_register(array($this, 'load'));
        $this->modulesPath = ( $modulesPath ? $modulesPath : _XE_PATH_.'modules' );
    }

    /**
     * Loader checks if the missing $class exists
     *
     * @param $class
     */
    public function load($class)
    {
        //compat autoloading
        if (is_readable($path = "{$this->modulesPath}/$class/$class.class.php")) {
            include_once $path;
            return;
        }
        //in-module PSR-0
        elseif (preg_match_all('/^GlCMS\\\\Module\\\\([^\\\\]+)(\\\\.+)?(\\\\.+)$/', $class, $matches, PREG_SET_ORDER)) {
            $matches = $matches[0];
            $matches[3] = ltrim($matches[3], '\\');
            $moduleName = strtolower($matches[1]);
            // only slashes for internal path
            $internalPath = str_replace('\\', '/', $matches[2]);
            // slashes and underscores for class name
            $classPath = str_replace(array('\\', '_'), '/', $matches[3]);
            $path = "{$this->modulesPath}/$moduleName$internalPath/$classPath.php";
            if (!is_readable($path)) {
                throw new \RuntimeException("[PSR-0 autoloading for module $moduleName] Could not load $path");
            }
            include_once $path;
            return;
        }
    }

}
