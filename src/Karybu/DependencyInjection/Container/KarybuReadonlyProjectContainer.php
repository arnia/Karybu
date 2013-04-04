<?php
namespace Karybu\DependencyInjection\Container;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * KarybuDevDebugProjectContainer
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class KarybuReadonlyProjectContainer extends Container
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->parameters = $this->getDefaultParameters();

        $this->services =
        $this->scopedServices =
        $this->scopeStacks = array();

        $this->set('service_container', $this);

        $this->scopes = array();
        $this->scopeChildren = array();
    }

    /**
     * Gets the 'cms.config.locator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\Config\ConfigLocator A Karybu\Config\ConfigLocator instance.
     */
    protected function getCms_Config_LocatorService()
    {
        return $this->services['cms.config.locator'] = new \Karybu\Config\ConfigLocator();
    }

    /**
     * Gets the 'cms.context.instance' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return ContextInstance A ContextInstance instance.
     */
    protected function getCms_Context_InstanceService()
    {
        return $this->services['cms.context.instance'] = new \ContextInstance(NULL, NULL, NULL, $this->get('cms.router.nodebug'));
    }

    /**
     * Gets the 'cms.display_handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return DisplayHandler A DisplayHandler instance.
     */
    protected function getCms_DisplayHandlerService()
    {
        $this->services['cms.display_handler'] = $instance = new \DisplayHandler();

        $instance->setGlobalGzEncoding(false);

        return $instance;
    }

    /**
     * Gets the 'cms.file_handler.instance' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return FileHandlerInstance A FileHandlerInstance instance.
     */
    protected function getCms_FileHandler_InstanceService()
    {
        return $this->services['cms.file_handler.instance'] = new \FileHandlerInstance();
    }

    /**
     * Gets the 'cms.mobile.instance' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return MobileInstance A MobileInstance instance.
     */
    protected function getCms_Mobile_InstanceService()
    {
        return $this->services['cms.mobile.instance'] = new \MobileInstance();
    }

    /**
     * Gets the 'cms.router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\Routing\Router A Karybu\Routing\Router instance.
     */
    protected function getCms_RouterService()
    {
        return $this->services['cms.router'] = new \Karybu\Routing\Router($this->get('cms.router.loader'), $this->get('context'), NULL, true);
    }

    protected function getCms_Router_NodebugService()
    {
        return $this->services['cms.router.nodebug'] = new \Karybu\Routing\Router($this->get('cms.router.loader'), $this->get('context'), NULL, false);
    }

    /**
     * Gets the 'cms.router.loader' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\Routing\Loader\YamlFileLoader A Karybu\Routing\Loader\YamlFileLoader instance.
     */
    protected function getCms_Router_LoaderService()
    {
        return $this->services['cms.router.loader'] = new \Karybu\Routing\Loader\YamlFileLoader($this->get('cms.config.locator'));
    }

    /**
     * Gets the 'context' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Symfony\Component\Routing\RequestContext A Symfony\Component\Routing\RequestContext instance.
     */
    protected function getContextService()
    {
        return $this->services['context'] = new \Symfony\Component\Routing\RequestContext();
    }

    /**
     * Gets the 'dispatcher' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Symfony\Component\EventDispatcher\EventDispatcher A Symfony\Component\EventDispatcher\EventDispatcher instance.
     */
    protected function getDispatcherService()
    {
        $this->services['dispatcher'] = $instance = new \Symfony\Component\EventDispatcher\EventDispatcher();

        $instance->addSubscriber($this->get('listener.router'));
        $instance->addSubscriber($this->get('listener.debug.toolbar'));
        $instance->addSubscriber($this->get('listener.cms'));
        $instance->addSubscriber($this->get('listener.response'));
        $instance->addSubscriber($this->get('listener.response.summary'));
        $instance->addSubscriber($this->get('listener.exception'));
        $instance->addSubscriber($this->get('listener.db.query_info'));
        $instance->addSubscriber($this->get('listener.db.slow_query'));
        $instance->addSubscriber($this->get('listener.db.errors'));

        return $instance;
    }

    /**
     * Gets the 'http_kernel' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\HttpKernel\HttpKernel A Karybu\HttpKernel\HttpKernel instance.
     */
    protected function getHttpKernelService()
    {
        $this->services['http_kernel'] = $instance = new \Karybu\HttpKernel\HttpKernel($this->get('dispatcher'), $this->get('resolver'));

        $instance->setDebug(true);

        return $instance;
    }

    /**
     * Gets the 'listener.cms' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\EventListener\CMSListener A Karybu\EventListener\CMSListener instance.
     */
    protected function getListener_CmsService()
    {
        return $this->services['listener.cms'] = new \Karybu\EventListener\CMSListener($this->get('cms.context.instance'), $this->get('cms.display_handler'), $this->get('cms.mobile.instance'), $this->get('cms.file_handler.instance'), $this->get('logger'));
    }

    /**
     * Gets the 'listener.db.errors' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\Module\Debug\EventListener\QueryErrorListener A Karybu\Module\Debug\EventListener\QueryErrorListener instance.
     */
    protected function getListener_Db_ErrorsService()
    {
        return $this->services['listener.db.errors'] = new \Karybu\Module\Debug\EventListener\QueryErrorListener($this->get('logger.db_errors'));
    }

    /**
     * Gets the 'listener.db.query_info' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\Module\Debug\EventListener\DBQueryInfoListener A Karybu\Module\Debug\EventListener\DBQueryInfoListener instance.
     */
    protected function getListener_Db_QueryInfoService()
    {
        return $this->services['listener.db.query_info'] = new \Karybu\Module\Debug\EventListener\DBQueryInfoListener($this->get('logger.db_info'));
    }

    /**
     * Gets the 'listener.db.slow_query' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\Module\Debug\EventListener\SlowQueryListener A Karybu\Module\Debug\EventListener\SlowQueryListener instance.
     */
    protected function getListener_Db_SlowQueryService()
    {
        return $this->services['listener.db.slow_query'] = new \Karybu\Module\Debug\EventListener\SlowQueryListener(800, $this->get('logger.db_slow_query'));
    }

    /**
     * Gets the 'listener.debug.toolbar' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\Module\Debug\EventListener\DebugToolbarListener A Karybu\Module\Debug\EventListener\DebugToolbarListener instance.
     */
    protected function getListener_Debug_ToolbarService()
    {
        $this->services['listener.debug.toolbar'] = $instance = new \Karybu\Module\Debug\EventListener\DebugToolbarListener($this->get('cms.context.instance'), 0);

        $instance->enableQueriesInfo($this->get('listener.db.query_info'));
        $instance->enableFailedQueriesInfo($this->get('listener.db.errors'));
        $instance->enablePHPErrorsInfo($this->get('listener.error.handler'));

        return $instance;
    }

    /**
     * Gets the 'listener.error.handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\EventListener\ErrorHandler A Karybu\EventListener\ErrorHandler instance.
     */
    protected function getListener_Error_HandlerService()
    {
        return $this->services['listener.error.handler'] = new \Karybu\EventListener\ErrorHandler();
    }

    /**
     * Gets the 'listener.exception' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\EventListener\ExceptionListener A Karybu\EventListener\ExceptionListener instance.
     */
    protected function getListener_ExceptionService()
    {
        return $this->services['listener.exception'] = new \Karybu\EventListener\ExceptionListener($this->get('logger.exceptions'));
    }

    /**
     * Gets the 'listener.response' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Symfony\Component\HttpKernel\EventListener\ResponseListener A Symfony\Component\HttpKernel\EventListener\ResponseListener instance.
     */
    protected function getListener_ResponseService()
    {
        return $this->services['listener.response'] = new \Symfony\Component\HttpKernel\EventListener\ResponseListener('UTF-8');
    }

    /**
     * Gets the 'listener.response.summary' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\Module\Debug\EventListener\ResponseSummaryInfoListener A Karybu\Module\Debug\EventListener\ResponseSummaryInfoListener instance.
     */
    protected function getListener_Response_SummaryService()
    {
        return $this->services['listener.response.summary'] = new \Karybu\Module\Debug\EventListener\ResponseSummaryInfoListener($this->get('logger'));
    }

    /**
     * Gets the 'listener.router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\EventListener\RouterListener A Karybu\EventListener\RouterListener instance.
     */
    protected function getListener_RouterService()
    {
        return $this->services['listener.router'] = new \Karybu\EventListener\RouterListener($this->get('cms.router.nodebug'));
    }

    /**
     * Gets the 'logger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Logger A Monolog\Logger instance.
     */
    protected function getLoggerService()
    {
        $this->services['logger'] = $instance = new \Monolog\Logger('cms');

       // $instance->pushHandler($this->get('logger.handler.stream'));

        return $instance;
    }

    /**
     * Gets the 'logger.db_errors' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Logger A Monolog\Logger instance.
     */
    protected function getLogger_DbErrorsService()
    {
        $this->services['logger.db_errors'] = $instance = new \Monolog\Logger('db');

//        $instance->pushHandler($this->get('logger.handler.stream.db_errors'));
//        $instance->pushHandler($this->get('logger.handler.stream.errors'));

        return $instance;
    }

    /**
     * Gets the 'logger.db_info' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Logger A Monolog\Logger instance.
     */
    protected function getLogger_DbInfoService()
    {
        $this->services['logger.db_info'] = $instance = new \Monolog\Logger('db');

//        $instance->pushHandler($this->get('logger.handler.stream.db_info'));

        return $instance;
    }

    /**
     * Gets the 'logger.db_slow_query' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Logger A Monolog\Logger instance.
     */
    protected function getLogger_DbSlowQueryService()
    {
        $this->services['logger.db_slow_query'] = $instance = new \Monolog\Logger('db');

//        $instance->pushHandler($this->get('logger.handler.stream.db_slow_query'));

        return $instance;
    }

    /**
     * Gets the 'logger.exceptions' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Logger A Monolog\Logger instance.
     */
    protected function getLogger_ExceptionsService()
    {
        $this->services['logger.exceptions'] = $instance = new \Monolog\Logger('cms');

//        $instance->pushHandler($this->get('logger.handler.stream.errors'));

        return $instance;
    }

    /**
     * Gets the 'logger.handler.stream' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Handler\StreamHandler A Monolog\Handler\StreamHandler instance.
     */
    protected function getLogger_Handler_StreamService()
    {
        return $this->services['logger.handler.stream'] = new \Monolog\Handler\StreamHandler('/home/razvan/www/karybu/files/logs/dev.log', 100);
    }

    /**
     * Gets the 'logger.handler.stream.db_errors' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Handler\StreamHandler A Monolog\Handler\StreamHandler instance.
     */
    protected function getLogger_Handler_Stream_DbErrorsService()
    {
        return $this->services['logger.handler.stream.db_errors'] = new \Monolog\Handler\StreamHandler('/home/razvan/www/karybu/files/logs/db_errors_dev.log', 100);
    }

    /**
     * Gets the 'logger.handler.stream.db_info' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Handler\StreamHandler A Monolog\Handler\StreamHandler instance.
     */
    protected function getLogger_Handler_Stream_DbInfoService()
    {
        return $this->services['logger.handler.stream.db_info'] = new \Monolog\Handler\StreamHandler('/home/razvan/www/karybu/files/logs/db_info_dev.log', 100);
    }

    /**
     * Gets the 'logger.handler.stream.db_slow_query' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Handler\StreamHandler A Monolog\Handler\StreamHandler instance.
     */
    protected function getLogger_Handler_Stream_DbSlowQueryService()
    {
        return $this->services['logger.handler.stream.db_slow_query'] = new \Monolog\Handler\StreamHandler('/home/razvan/www/karybu/files/logs/db_slow_query_dev.log', 100);
    }

    /**
     * Gets the 'logger.handler.stream.errors' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Monolog\Handler\StreamHandler A Monolog\Handler\StreamHandler instance.
     */
    protected function getLogger_Handler_Stream_ErrorsService()
    {
        return $this->services['logger.handler.stream.errors'] = new \Monolog\Handler\StreamHandler('/home/razvan/www/karybu/files/logs/errors_dev.log', 100);
    }

    /**
     * Gets the 'resolver' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Karybu\HttpKernel\Controller\ControllerResolver A Karybu\HttpKernel\Controller\ControllerResolver instance.
     */
    protected function getResolverService()
    {
        return $this->services['resolver'] = new \Karybu\HttpKernel\Controller\ControllerResolver();
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        $name = strtolower($name);

        if (!(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters))) {
            throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($name)
    {
        $name = strtolower($name);

        return isset($this->parameters[$name]) || array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $this->parameterBag = new FrozenParameterBag($this->parameters);
        }

        return $this->parameterBag;
    }
    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'kernel.root_dir' => '/home/razvan/www/karybu/',
            'kernel.environment' => 'dev',
            'kernel.debug' => false,
            'kernel.name' => 'Karybu',
            'kernel.cache_dir' => '/home/razvan/www/karybu/files/cache/dev',
            'kernel.logs_dir' => '/home/razvan/www/karybu/files/logs',
            'kernel.bundles' => array(
                'DebugModule' => 'Karybu\\Module\\Debug\\DebugModule',
            ),
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => 'KarybuDevDebugProjectContainer',
            'cms.gz_encoding' => false,
            'charset' => 'UTF-8',
            'logger.debug.level' => 100,
            'logger.debug.toolbar' => 0,
            'logger.slow_queries_threshold' => 800,
        );
    }
}
