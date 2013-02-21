<?php
// florin, 2/21/13, 8:01 PM

namespace GlCMS\Autoloader\Loader;

class PsrLoader extends AbstractLoader
{

    /**
     * In-module PSR-0
     * @see http://zaemis.blogspot.ro/2012/05/writing-minimal-psr-0-autoloader.html
     **/
    public function load($class)
    {
        if (preg_match_all('/^GlCMS\\\\Module\\\\([^\\\\]+)(\\\\.+)?(\\\\.+)$/', $class, $matches, PREG_SET_ORDER)) {
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
            return $this->includeFile($path);
        }
        return false;
    }

}
