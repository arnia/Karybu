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
        $this->containerBuilder->setParameter('log_slow_query', __LOG_SLOW_QUERY__);
        $this->containerBuilder->setParameter('log_slow_query_min_duration', __LOG_SLOW_QUERY__);
        $this->containerBuilder->setParameter('log_query_errors', __DEBUG_DB_OUTPUT__);
        $this->containerBuilder->setParameter('show_request_response_info', __DEBUG__ & 2);
        $this->containerBuilder->setParameter('show_db_queries_info', __DEBUG__ & 4);
        $this->containerBuilder->setParameter('log_info_in_firebug_console', __DEBUG_OUTPUT__ == 2);


        // TODO Enable and disable logging based on the parameters above - maybe load a different container?
        $this->register('logger.handler', 'Monolog\Handler\StreamHandler')
            ->setArguments(array('%kernel.logs_dir%/%kernel.environment%.log', Logger::DEBUG));
        $this->register('logger.errors', 'Monolog\Handler\StreamHandler')
            ->setArguments(array('%kernel.logs_dir%/errors_%kernel.environment%.log', Logger::DEBUG));
        $this->register('logger.handler.db_info', 'Monolog\Handler\StreamHandler')
            ->setArguments(array('%kernel.logs_dir%/db_info_%kernel.environment%.log', Logger::DEBUG));
        $this->register('logger.handler.db_slow_query', 'Monolog\Handler\StreamHandler')
            ->setArguments(array('%kernel.logs_dir%/db_slow_query_%kernel.environment%.log', Logger::DEBUG));
        $this->register('logger.handler.db_errors', 'Monolog\Handler\StreamHandler')
            ->setArguments(array('%kernel.logs_dir%/db_errors_%kernel.environment%.log', Logger::DEBUG));

        $this->register('logger', 'Monolog\Logger')
            ->setArguments(array('cms'))
            ->addMethodCall('pushHandler', array(new Reference('logger.handler')));
        $this->register('logger.exceptions', 'Monolog\Logger')
            ->setArguments(array('cms'))
            ->addMethodCall('pushHandler', array(new Reference('logger.errors')));
        $this->register('logger.db_info', 'Monolog\Logger')
            ->setArguments(array('db'))
            ->addMethodCall('pushHandler', array(new Reference('logger.handler.db_info')));
        $this->register('logger.db_slow_query', 'Monolog\Logger')
            ->setArguments(array('db'))
            ->addMethodCall('pushHandler', array(new Reference('logger.handler.db_slow_query')));
        $this->register('logger.db_errors', 'Monolog\Logger')
            ->setArguments(array('db'))
            ->addMethodCall('pushHandler', array(new Reference('logger.handler.db_errors')))
            ->addMethodCall('pushHandler', array(new Reference('logger.errors')));

        //$this->register("database", "DB")->addMethodCall("setLogger", array(new Reference("logger")));

        $this->register('cms.config.locator', 'GlCMS\Config\ConfigLocator');
        $this->register('cms.router.loader', 'GlCMS\Routing\Loader\YamlFileLoader')->setArguments(array(new Reference('cms.config.locator')));
        $this->register('context', 'Symfony\Component\Routing\RequestContext');
        $this->register('cms.router', 'GlCMS\Routing\Router')->setArguments(array(new Reference('cms.router.loader'), new Reference('context'), null, '%debug%'));
        $this->register('cms.context.instance', 'ContextInstance')->setArguments(array(null, null, null, new Reference('cms.router')));

        $this->register('listener.router', 'GlCMS\EventListener\RouterListener')->setArguments(array(new Reference('cms.router')));
        $this->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')->setArguments(array('%charset%'));
        $this->register('listener.exception', 'GlCMS\EventListener\ExceptionListener')
            ->setArguments(array(new Reference("logger.exceptions")));
        $this->register('listener.cms', 'GlCMS\EventListener\CMSListener')->setArguments(array(new Reference('cms.context.instance'), new Reference('logger')));

        $this->register('listener.debug.toolbar', 'GlCMS\Module\DebugToolbar\EventListener\DebugToolbarListener')
            ->setArguments(array(new Reference('cms.context.instance'), '%debug%'));

        $this->register('listener.db.query_info', 'GlCMS\EventListener\Debug\DBQueryInfoListener')
            ->setArguments(array(new Reference('logger.db_info')));
        $this->register('listener.db.slow_query', 'GlCMS\EventListener\Debug\SlowQueryListener')
            ->setArguments(array('%log_slow_query_min_duration%', new Reference('logger.db_slow_query')));
        $this->register('listener.db.errors', 'GlCMS\EventListener\Debug\QueryErrorListener')
            ->setArguments(array(new Reference('logger.db_errors')));

        // listener around Response, used to aggregate summary statistics
        $this->register('listener.response.summary', 'GlCMS\EventListener\Debug\ResponseSummaryInfoListener')
            ->setArguments(array(new Reference('logger')));

        $this->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
            ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.debug.toolbar')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.cms')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response.summary')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.exception')))
            ->addMethodCall('addSubscriber', array(new Reference("listener.db.query_info")))
            ->addMethodCall('addSubscriber', array(new Reference("listener.db.slow_query")))
            ->addMethodCall('addSubscriber', array(new Reference("listener.db.errors")));

        $this->register('resolver', 'GlCMS\HttpKernel\Controller\ControllerResolver');
        $this->register('http_kernel', 'GlCMS\HttpKernel\HttpKernel')->setArguments(array(new Reference('dispatcher'), new Reference('resolver')));
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