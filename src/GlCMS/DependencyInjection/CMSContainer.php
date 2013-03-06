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

        $this->register('cms.config.locator', 'GlCMS\Config\ConfigLocator');
        $this->register('cms.router.loader', 'GlCMS\Routing\Loader\YamlFileLoader')->setArguments(array(new Reference('cms.config.locator')));
        $this->register('context', 'Symfony\Component\Routing\RequestContext');
        $this->register('cms.router', 'GlCMS\Routing\Router')->setArguments(array(new Reference('cms.router.loader'), new Reference('context'), null, '%debug%'));

        $this->register('listener.router', 'GlCMS\EventListener\RouterListener')->setArguments(array(new Reference('cms.router')));
        $this->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')->setArguments(array('%charset%'));
        $this->register('listener.exception', 'GlCMS\EventListener\ExceptionListener');

        $this->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
            ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.cms')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.exception')));

        $this->register('resolver', 'GlCMS\HttpKernel\Controller\ControllerResolver');
        $this->register('http_kernel', 'GlCMS\HttpKernel\HttpKernel')->setArguments(array(new Reference('dispatcher'), new Reference('resolver')));
        $this->register('cms.context.instance', 'ContextInstance')->setArguments(array(null, null, null, new Reference('cms.router')));
        $this->register('listener.cms', 'GlCMS\EventListener\CMSListener')->setArguments(array(new Reference('cms.context.instance')));
    }

    // mirrors

    public function get($id)
    {
        return $this->containerBuilder->get($id);
    }

    public function register($id, $class = null)
    {
        return $this->containerBuilder->register($id, $class);
    }

}