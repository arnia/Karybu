<?php
// florin, 2/1/13, 2:32 PM
namespace GlCMS\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;

class Kernel extends SymfonyKernel implements KernelInterface
{
    protected $modules = array();

    public function registerBundles()
    {
        return array(
            new \GlCMS\Module\Shop\Shop()
        );
    }

    /**
     * Don't execute parent init because it messes with XE's settings
     */
    public function init()
    {
    }

    public function boot()
    {
        parent::boot();

        $this->initializeModules();

        foreach ($this->getModules() as $module) {
            $module->setContainer($this->container);
            $module->boot();
        }
    }

    /**
     * just like for bundles, but with the inheritance part stripped
     *
     * @throws \LogicException
     */
    protected function initializeModules()
    {
        $this->modules = array();
        foreach ($this->registerModules() as $module) {
            $name = $module->getName();
            if (isset($this->modules[$name])) {
                throw new \LogicException(sprintf('Trying to register two modules with the same name "%s"', $name));
            }
            $this->modules[$name] = $module;
        }
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        //$loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
        $loader->load(_XE_PATH_.'/config/config.yml');
    }

    /**
     * Initializes the service container.
     *
     * The cached version of the service container is used when fresh, otherwise the
     * container is built.
     */
    protected function initializeContainer()
    {
        $class = $this->getContainerClass();
        $cache = new \Symfony\Component\Config\ConfigCache($this->getCacheDir().'/'.$class.'.php', $this->debug);
        $fresh = true;
        if (!$cache->isFresh()) {
            $container = $this->buildContainer();
            $this->dumpContainer($cache, $container, $class, $this->getContainerBaseClass());

            $fresh = false;
        }

        require_once $cache;

        $this->container = new $class();
        $this->container->set('kernel', $this);

        if (!$fresh && $this->container->has('cache_warmer')) {
            $this->container->get('cache_warmer')->warmUp($this->container->getParameter('kernel.cache_dir'));
        }
    }

    /**
     * @return array Array of modules
     */
    public function registerModules()
    {
        $modules = $this->getCoreModules();
        return array_merge($modules, array(

            ));
    }

    final public function getCoreModules()
    {
        return array();
    }

    public function getCacheDir()
    {
        return $this->rootDir . 'files/cache/env/' . $this->environment;
    }

    /**
     * Gets the application root dir.
     *
     * @return string The application root dir
     */
    public function getRootDir()
    {
        return _XE_PATH_;
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     */
    public function getLogDir()
    {
        return $this->rootDir.'/files/logs';
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return $this->name.ucfirst($this->environment).($this->debug ? 'Debug' : '').'ProjectContainer';
    }

    /**
     * Gets the name of the kernel
     *
     * @return string The kernel name
     *
     * @api
     */
    public function getName()
    {
        if (null === $this->name) {
            //$this->name = preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->rootDir));
            $this->name = 'GlCMS';
        }

        return $this->name;
    }

}
