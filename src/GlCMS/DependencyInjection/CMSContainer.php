<?php
namespace GlCMS\DependencyInjection;

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class CMSContainer
{
    public $containerBuilder;

    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        $this->containerBuilder = new ContainerBuilder($parameterBag);
        $this->registerCMSContainer();
    }

    public function registerCMSContainer()
    {
        $this->containerBuilder->setParameter('debug', true);
        $this->containerBuilder->setParameter('charset', 'UTF-8');
        //$this->containerBuilder->setParameter('routes', include(_XE_PATH_ . '/src/routes.php'));

        $this->containerBuilder->register('context', 'Symfony\Component\Routing\RequestContext');
        $this->containerBuilder->register('resolver', 'GlCMS\HttpKernel\Controller\ControllerResolver');

        $this->containerBuilder->register('cms.routes', 'GlCMS\Routing\RouteCollection');
        $this->containerBuilder->register('matcher', 'Symfony\Component\Routing\Matcher\UrlMatcher')->setArguments(array(new Reference('cms.routes'), new Reference('context')));
        $this->containerBuilder->register('listener.router', 'GlCMS\EventListener\RouterListener')
            ->setArguments(array(new Reference('matcher')));
        $this->containerBuilder->register('listener.cms', 'GlCMS\EventListener\CMSListener');
        $this->containerBuilder->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')
            ->setArguments(array('%charset%'));
        $this->containerBuilder->register('listener.exception', 'GlCMS\EventListener\ExceptionListener');
        $this->containerBuilder->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
            ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.cms')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.exception')));
        $this->containerBuilder->register('http_kernel', 'GlCMS\HttpKernel\HttpKernel')->setArguments(array(new Reference('dispatcher'), new Reference('resolver')));
    }

}