<?php
// florin, 2/1/13, 2:32 PM
namespace GlCMS\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Kernel extends SymfonyKernel
{
    protected $modules = array();

    public function registerBundles()
    {
        return array(
            new \GlCMS\Module\Debug\DebugModule()
        );
    }

    /**
     * Don't execute parent init because it messes with XE's settings
     */
    public function init()
    {
        
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return
     */
    protected function getContainerBuilder()
    {
        $this->container = new \GlCMS\DependencyInjection\CMSContainer(new ParameterBag($this->getKernelParameters()));
        $this->container->containerBuilder->set('kernel', $this);
        return $this->container->containerBuilder;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(_XE_PATH_.'config/container_'.$this->getEnvironment().'.yml');
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

    public function getCacheDir()
    {
        return $this->rootDir . 'files/cache/' . $this->environment;
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
        return $this->rootDir.'files/logs';
    }

    /**
     * Gets the name of the kernel
     *
     * @return string The kernel name
     */
    public function getName()
    {
        return 'GlCMS';
    }

}