<?php
// florin, 2/21/13, 7:36 PM

namespace GlCMS\Autoloader\Loader;

use GlCMS\Autoloader\Loader\LoaderInterface;

abstract class AbstractLoader implements LoaderInterface
{
    protected $modulesPath;
    protected $className;
    protected $loaded;

    public function __construct($whatToLoad=null, $modulesPath=null)
    {
        $this->modulesPath = ( $modulesPath ? $modulesPath : _XE_PATH_.'modules' );
        if ($whatToLoad) {
            $this->className = $whatToLoad;
            $this->loaded = $this->load($this->className);
        }
    }

    public function isOk()
    {
        return $this->loaded;
    }

    protected function includeFile($path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("File '$path' does not exist.");
        }
        if (!is_readable($path)) {
            throw new \RuntimeException("File '$path' is not readable.");
        }
        return include_once($path);
    }

    protected function classHasNamespace($class)
    {
        return strpos($class, '\\') ? true : false;
    }
}
