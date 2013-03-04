<?php
// florin, 2/21/13, 7:32 PM

namespace GlCMS\Autoloader\Loader;

/**
 * Backward compatibility loader for old cms
 */
class CompatLoader extends AbstractLoader
{
    public function load($class)
    {
        if ($loaded = $this->loadSimplePath($class)) {
            return $loaded;
        }
        if ($loaded = $this->loadCamelCasePath($class)) {
            return $loaded;
        }
        return false;
    }

    /**
     * page => modules/page/page.class.php
     */
    public function loadSimplePath($class)
    {
        if ($this->classHasNamespace($class)) {
            return false;
        }
        $path = "{$this->modulesPath}/$class/$class.class.php";
        return $this->includeFile($path);
    }

    /**
     * pageView => modules/page/page.view.php
     * pageAdminController => modules/page/page.admin.controller.php
     */
    public function loadCamelCasePath($class)
    {
        if ($this->classHasNamespace($class)) {
            return false;
        }
        if (preg_match_all('#((?:^|[A-Z])[a-z]+)#', $class, $matches)) {
            $matches = $matches[0];
            $module = $matches[0];
            $name = strtolower(implode('.', $matches));
            $path = "{$this->modulesPath}/$module/$name.php";
            return $this->includeFile($path);
        }
        return false;
    }
}
