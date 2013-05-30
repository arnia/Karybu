<?php
// florin, 2/21/13, 8:01 PM

namespace Karybu\Autoloader\Loader;

class PsrLoader extends AbstractLoader
{

    /**
     * In-module PSR-0
     * @see http://zaemis.blogspot.ro/2012/05/writing-minimal-psr-0-autoloader.html
     **/
    public function load($class)
    {
        if (preg_match_all('/^Karybu\\\\Module\\\\([^\\\\]+)(\\\\.+)?(\\\\.+)$/', $class, $matches, PREG_SET_ORDER)) {
            $matches = $matches[0];
            $matches[3] = ltrim($matches[3], '\\');
            $moduleName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $matches[1]));
            // only slashes for internal path
            $internalPath = str_replace('\\', '/', $matches[2]);
            // slashes and underscores for class name
            $classPath = str_replace(array('\\', '_'), '/', $matches[3]);
            $path = "{$this->modulesPath}/$moduleName/src$internalPath/$classPath.php";
            return $this->includeFile($path);
        }
        return false;
    }

}
