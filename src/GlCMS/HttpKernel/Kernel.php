<?php
// florin, 2/1/13, 2:32 PM
namespace GlCMS\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Kernel #implements KernelInterface
{
    /** @var \Symfony\Component\DependencyInjection\ContainerBuilder */
    public $container;
    public $booted;
    public $debug;
    public $startTime;

    public function __construct($debug = false)
    {
        $this->booted = false;
        $this->classes = array();
        $this->debug = (Boolean) $debug;
        if ($this->debug) $this->startTime = microtime(true);
        $this->init();
    }

    public function init()
    {
/*        if ($this->debug) {
            ini_set('display_errors', 1);
            error_reporting(-1);

            //DebugClassLoader::enable();
            //ErrorHandler::register($this->errorReportingLevel);
            if ('cli' !== php_sapi_name()) {
                //ExceptionHandler::register();
            }
        } else {
            ini_set('display_errors', 0);
        }*/
    }

    public function boot()
    {
        if (true === $this->booted) return;
        // init modules
        // $this->initializeModules();
        $this->initializeContainer();
        $this->booted = true;
    }

    /**
     * Initializes the service container.
     *
     * The cached version of the service container is used when fresh, otherwise the
     * container is built.
     */
    protected function initializeContainer()
    {
        $cmsContainer = new \GlCMS\DependencyInjection\CMSContainer();
        $this->container = $cmsContainer->getServiceContainer();
        $this->container->set('kernel', $this);
    }

    /**
     * Gets a http kernel from the container
     *
     * @return \Symfony\Component\HttpKernel\HttpKernel
     */
    protected function getHttpKernel()
    {
        $containerBuilder = $this->container;
        return $containerBuilder->get('http_kernel');
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (false === $this->booted) {
            $this->boot();
        }
        $httpKernel = $this->getHttpKernel();
        return $httpKernel->handle($request, $type, $catch);
    }

    public function terminate(Request $request, Response $response)
    {
        if (false === $this->booted) {
            return;
        }
        if ($this->getHttpKernel() instanceof TerminableInterface) {
            $this->getHttpKernel()->terminate($request, $response);
        }
    }

}
