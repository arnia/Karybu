<?php
// florin, 2/1/13, 2:32 PM
namespace GlCMS\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;

class Kernel extends SymfonyKernel implements KernelInterface
{
    protected $modules = array();

    public function registerBundles() {
        return array();
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

    /**
     * Initializes the service container.
     */
    protected function initializeContainer()
    {
        //TODO if needed, implement the container cache that was stripped from parent
        $cmsContainer = new \GlCMS\DependencyInjection\CMSContainer();
        $this->container = $cmsContainer->getServiceContainer();
        $this->container->set('kernel', $this);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(_XE_PATH_.'/config/config_'.$this->getEnvironment().'.yml');
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

}
