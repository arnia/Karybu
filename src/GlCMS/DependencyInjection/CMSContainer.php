<?php
namespace GlCMS\DependencyInjection;

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CMSContainer
{
    public $serviceContainer;

    public function __construct()
    {
        $this->serviceContainer = new DependencyInjection\ContainerBuilder();
        $this->init();
    }

    public function init()
    {
        $this->serviceContainer->setParameter('debug', true);
        $this->serviceContainer->setParameter('charset', 'UTF-8');
        $this->serviceContainer->setParameter('routes', include(_XE_PATH_ . '/src/routes.php'));

        $this->serviceContainer->register('context', 'Symfony\Component\Routing\RequestContext');
        $this->serviceContainer->register('resolver', 'GlCMS\HttpKernel\Controller\ControllerResolver');
        $this->serviceContainer->register('matcher', 'Symfony\Component\Routing\Matcher\UrlMatcher')->setArguments(array('%routes%', new Reference('context')));
        $this->serviceContainer->register('listener.router', 'GlCMS\EventListener\RouterListener')
            ->setArguments(array(new Reference('matcher')));
        $this->serviceContainer->register('listener.cms', 'GlCMS\EventListener\CMSListener');
        $this->serviceContainer->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')
            ->setArguments(array('%charset%'));
        $this->serviceContainer->register('listener.exception', 'GlCMS\EventListener\ExceptionListener');
        $this->serviceContainer->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
            ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.cms')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.exception')));
        $this->serviceContainer->register('http_kernel', 'GlCMS\HttpKernel\HttpKernel')->setArguments(array(new Reference('dispatcher'), new Reference('resolver')));
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Gets a service.
     *
     * @param string  $id              The service identifier
     * @param integer $invalidBehavior The behavior when the service does not exist
     *
     * @return object The associated service
     *
     * @throws \InvalidArgumentException if the service is not defined
     * @throws \LogicException if the service has a circular reference to itself
     */
    public function get($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->serviceContainer->get($id, $invalidBehavior);
    }

}

/*
 * loading settings from a (yml?) file:
 * http://symfony.com/doc/current/components/dependency_injection/introduction.html#setting-up-the-container-with-configuration-files
 */