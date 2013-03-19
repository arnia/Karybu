<?php
namespace GlCMS\EventListener;

use GlCMS;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CMSListener implements EventSubscriberInterface
{
    private $logger;

    /** @var \ContextInstance */
    private $cmsContext;

    /**
     * We're injecting into the HttpKernel's events:
     * http://symfony.com/doc/master/components/http_kernel/introduction.html
     *
     * @see RouterListener
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('setupLegacyDependencies', 50),
                array('initializeContextRequestArguments', 48),
                array('initializeDatabaseSettings', 46),
                //32 is router listener
                array('doContextInit', 30),
                array('doContextCheckSSO', 28),
                array('checkModuleHandlerInit', 26),
                array('prepareRequestForResolving', 24),
                array('checkForErrorsAndPrepareMobileStatus', 22)
            ),
            KernelEvents::CONTROLLER => array(
                array('filterController', 100),
                array('executeTriggersAddonsAndOthersBefore', 99)
            ),
            KernelEvents::EXCEPTION => array('onKernelException', -128),
            KernelEvents::TERMINATE => 'onTerminate',
            KernelEvents::VIEW => array(
                array('executeTriggersAddonsAndOthersAfter', 100),
                array('makeResponse', 99),
            )
        );
    }

    /**
     * @param \ContextInstance $cmsContext CMS context
     * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger The logger
     */
    public function __construct(\ContextInstance $cmsContext, LoggerInterface $logger = null)
    {
        $this->cmsContext = $cmsContext;
        $this->logger = $logger;
    }

    /**
     * Sets up any dependencies needed for the legacy code to work
     *
     * For now, takes care of:
     *  - initializing a ContextInstance for the Context class, to make static calls work
     *  - putting certain attributes of the ContextInstance class in $_GLOBALS, to make them accesible for the template files
     *  - putting the ContextInstance instance in the Request, to make
     *
     * @param GetResponseEvent $event
     */
    public function setupLegacyDependencies(GetResponseEvent $event)
    {
        // 1. Initialize Context instance and Mobile instance for legacy XE static calls
        \Context::setRequestContext($this->cmsContext);
        $mobile = new \MobileInstance();
        \Mobile::setRequestMobileInfo($mobile);

        // 2. Put context in GLOBALS - needed for template files
        $this->cmsContext->linkContextToGlobals();

        // 3. Put ContextInstance in Request - so that other listeners could setup context variables
        // TODO Refactor and remove this
        $request = $event->getRequest();
        // TODO Create a separate list of 'legacy' request attributes: like $request->attributes->legacy->set('oContext',$oContext);
        // Could be done by $request->attributes->set('legacy', new ParametersBag()); or something
        $request->attributes->set('oContext', $this->cmsContext);
    }

    /**
     * Saves $_GET and $_POST values in Context
     *
     * @param GetResponseEvent $event
     */
    public function initializeContextRequestArguments(GetResponseEvent $event)
    {
        $this->cmsContext->initializeRequestArguments();
    }

    /**
     * Loads database settings from db.config.php
     *
     * @param GetResponseEvent $event
     */
    public function initializeDatabaseSettings(GetResponseEvent $event)
    {
        $this->cmsContext->initializeDatabaseSettings();
    }

    /**
     * Do the rest of init
     */
    public function doContextInit(GetResponseEvent $event)
    {
        $this->cmsContext->init();
    }

    public function checkModuleHandlerInit(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        /** @var $oContext \Context */
        $oContext = $event->getRequest()->attributes->get('oContext');
        $oModuleHandler = new \ModuleHandlerInstance($oContext);
        \ModuleHandler::setModuleHandler($oModuleHandler);
        $init = $oModuleHandler->init();
        if ($init instanceof RedirectResponse) {
            $event->setResponse($init);
        }
        elseif (!$init) {
            $event->setResponse(new Response('Module handler init failure', 500));
        }
        else {
            $request->attributes->set('oModuleHandler', $oModuleHandler);
        }
    }

    public function doContextCheckSSO(GetResponseEvent $event)
    {
        /** @var $oContext \Context */
        $oContext = $event->getRequest()->attributes->get('oContext');
        if (($result = $oContext->checkSSO()) instanceof RedirectResponse) {
            $event->setResponse($result);
        }
    }

    public function checkForErrorsAndPrepareMobileStatus(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        /** @var $oModuleHandler \ModuleHandler */
        $oModuleHandler = $event->getRequest()->attributes->get('oModuleHandler');
        if ($errorObject = $oModuleHandler->checkForErrorsAndPrepareMobileStatus()) {
            /**
             * we have a cms error
             */
            $request->attributes->set('error', $errorObject);
        }
    }

    public function filterController(FilterControllerEvent $event)
    {
        /** @var $controller GlCMS\HttpKernel\Controller\ControllerWrapper */
        $controller = $event->getController();
        $oModule = $controller->getModuleInstance();
        $oModuleHandler = $event->getRequest()->attributes->get('oModuleHandler');
        if ($errorObject = $oModuleHandler->filterController($oModule)) {
            $controller->setModuleInstance($errorObject);
            $event->setController($controller);
        }
    }

    public function executeTriggersAddonsAndOthersBefore(FilterControllerEvent $event)
    {
        /** @var $controller GlCMS\HttpKernel\Controller\ControllerWrapper */
        $controller = $event->getController();
        $oModule = $controller->getModuleInstance();

        $procResult = $oModule->preProc();
        if ($procResult === false) {
            $oModule->skipAct = true;
            $controller->setModuleInstance($oModule);
            $event->setController($controller);
        }

        // Save state of before triggers, so that we can set session errors later
        $event->getRequest()->attributes->set('procResult', $procResult);
    }

    public function executeTriggersAddonsAndOthersAfter(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        $oModule = $result["oModule"];
        $output = $result["output"];

        $procResult = $oModule->postProc($output);
        if ($procResult === null || $procResult === true) {
            $procResult = $event->getRequest()->attributes->get('procResult');
        }

        $oModuleHandler = $event->getRequest()->attributes->get('oModuleHandler');
        $oModuleHandler->setErrorsToSessionAfterProc($oModule, $procResult);
    }

    public function makeResponse(GetResponseForControllerResultEvent $event)
    {
        $controllerResult = $event->getControllerResult();
        $oModule = $controllerResult["oModule"];

        // Load layouts and stuff like that
        /** @var $oModuleHandler \ModuleHandler */
        $oModuleHandler = $event->getRequest()->attributes->get('oModuleHandler');
        $oModuleHandler->displayContent($oModule);

        /**
         * Prepare Response object and cms display handler (which manages page headers and content)
         * We'll copy from $oDisplayHandler to $response and then set event $response
         */
        $oDisplayHandler = new \DisplayHandler();
        $response = new Response();

        // 1. Status code
        $status_code = $oDisplayHandler->getStatusCode($oModule);
        $response->setStatusCode($status_code[0], $status_code[1]);

        // 2. Headers
        $headers = $oDisplayHandler->getHeaders($oModule);
        foreach ($headers as $header) {
            $response->headers->set($header[0], $header[1], $header[2]);
        }

        // 3. Location header
        $lookingForLocation = headers_list();
        foreach ($lookingForLocation as $header) {
            $hSplit = explode(':', $header, 2);
            $hTarget = trim($hSplit[1]); $hName = trim($hSplit[0]);
            if (strtolower($hName) == 'location') {
                header_remove('location');
                $response = new RedirectResponse($hTarget);
            }
        }

        // 4. The content
        if (!($response instanceof RedirectResponse)) {
            $content = $oDisplayHandler->getContent($oModule);
            $response->setContent($content);
        }

        $event->setResponse($response);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        //$exception = $event->getException();
        //$event->setResponse(new Response($exception->getMessage(), 500));
    }

    public function prepareRequestForResolving(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $module_handler = $request->attributes->get('oModuleHandler');
        $request->attributes->set('act', $module_handler->act);
        $request->attributes->set('module', $module_handler->module);
        $request->attributes->set('is_mobile', \Mobile::isFromMobilePhone());
        $request->attributes->set('is_installed', \Context::isInstalled());
        $request->attributes->set('module_info', $module_handler->module_info);
    }

    public function onTerminate(PostResponseEvent $event)
    {
        $content = $event->getResponse()->getContent();
        // call a trigger after display
        \ModuleHandler::triggerCall('display', 'after', $content);

        /** @var $oContext \Context */
        $oContext = $event->getRequest()->attributes->get('oContext');
        $oContext->close();
    }

}
