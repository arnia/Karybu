<?php
namespace GlCMS\DependencyInjection;

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


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

        $this->register('logger.handler', 'Monolog\Handler\StreamHandler')
            ->setArguments(array('%kernel.logs_dir%/%kernel.environment%.log', Logger::DEBUG));
        $this->register('logger.handler.db', 'Monolog\Handler\StreamHandler')
            ->setArguments(array('%kernel.logs_dir%/db_%kernel.environment%.log', Logger::DEBUG));

        $this->register('logger', 'Monolog\Logger')
            ->setArguments(array('cms'))
            ->addMethodCall('pushHandler', array(new Reference('logger.handler')));
        $this->register('logger.db', 'Monolog\Logger')
            ->setArguments(array('db'))
            ->addMethodCall('pushHandler', array(new Reference('logger.handler.db')));

        //$this->register("database", "DB")->addMethodCall("setLogger", array(new Reference("logger")));

        $this->register('cms.config.locator', 'GlCMS\Config\ConfigLocator');
        $this->register('cms.router.loader', 'GlCMS\Routing\Loader\YamlFileLoader')->setArguments(array(new Reference('cms.config.locator')));
        $this->register('context', 'Symfony\Component\Routing\RequestContext');
        $this->register('cms.router', 'GlCMS\Routing\Router')->setArguments(array(new Reference('cms.router.loader'), new Reference('context'), null, '%debug%'));

        $this->register('listener.router', 'GlCMS\EventListener\RouterListener')->setArguments(array(new Reference('cms.router')));
        $this->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')->setArguments(array('%charset%'));
        $this->register('listener.exception', 'GlCMS\EventListener\ExceptionListener');
        $this->register('listener.cms', 'GlCMS\EventListener\CMSListener')->setArguments(array(new Reference('cms.context.instance'), new Reference('logger')));
        // listener around DB query
        $this->register('listener.db.query_info', 'GlCMS\EventListener\Debug\DBQueryInfoListener')
            ->setArguments(array(new Reference('logger.db')));
        // listener for debugging purposes, around Request
        $this->register('listener.debug', 'GlCMS\EventListener\DebugListener')
            ->addMethodCall('addDBListener', array(new Reference("listener.db.query_info")));
        // listener around Response, used to aggregate summary statistics
        $this->register('listener.response.summary',
            'GlCMS\EventListener\Debug\ResponseSummaryInfoListener');

        $this->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
            ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.debug')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.cms')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response.summary')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.exception')));

        $this->register('resolver', 'GlCMS\HttpKernel\Controller\ControllerResolver');
        $this->register('http_kernel', 'GlCMS\HttpKernel\HttpKernel')->setArguments(array(new Reference('dispatcher'), new Reference('resolver')));
        $this->register('cms.context.instance', 'ContextInstance')->setArguments(array(null, null, null, new Reference('cms.router')));



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