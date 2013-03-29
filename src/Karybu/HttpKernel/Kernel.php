<?php
// florin, 2/1/13, 2:32 PM
namespace Karybu\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;

use Karybu\EventListener\ExceptionHandler;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\Yaml\Yaml;

class Kernel extends SymfonyKernel
{
    protected $modules = array();

    public function registerBundles()
    {
        return array(
            new \Karybu\Module\Debug\DebugModule()
        );
    }

    /**
     * Don't execute parent init because it messes with XE's settings
     */
    public function init()
    {
        if ('cli' !== php_sapi_name()) {
            ExceptionHandler::register($this->debug);
        } else {
            ini_set('display_errors', 1);
        }
        Yaml::enablePhpParsing();
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(_XE_PATH_ . "config/config_{$this->getEnvironment()}.yml");
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return $this->getName() . ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
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
        return $this->rootDir . 'files/logs';
    }

    /**
     * Gets the name of the kernel
     *
     * @return string The kernel name
     */
    public function getName()
    {
        return 'Karybu';
    }

}