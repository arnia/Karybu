<?php
namespace GlCMS\EventListener;

use GlCMS;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CMSListener implements EventSubscriberInterface
{
    private $matcher;
    private $context;
    private $logger;

    /**
     * We're injecting into the HttpKernel workflow:
     * http://symfony.com/doc/master/components/http_kernel/introduction.html
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('doContextGlobalsLink', 34),
                array('routingListener', 32),
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
                array('onView', 99)
            )
        );
    }

    /**
     * @param UrlMatcherInterface|RequestMatcherInterface $matcher The Url or Request matcher
     * @param RequestContext|null                         $context The RequestContext (can be null when $matcher implements RequestContextAwareInterface)
     * @param LoggerInterface|null                        $logger  The logger
     */
    public function __construct($matcher, RequestContext $context = null, LoggerInterface $logger = null)
    {
        if (!$matcher instanceof UrlMatcherInterface && !$matcher instanceof RequestMatcherInterface) {
            throw new \InvalidArgumentException('Matcher must either implement UrlMatcherInterface or RequestMatcherInterface.');
        }
        if (null === $context && !$matcher instanceof RequestContextAwareInterface) {
            throw new \InvalidArgumentException('You must either pass a RequestContext or the matcher must implement RequestContextAwareInterface.');
        }
        $this->matcher = $matcher;
        $this->context = $context ?: $matcher->getContext();
        $this->logger = $logger;
    }

    /**
     * Set context variables in $GLOBALS (to use in display handler)
     */
    public function doContextGlobalsLink(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $oContext = \Context::getInstance();
        // This is the first code from the old cms
        $oContext->context = &$GLOBALS['__Context__'];
        $request->attributes->set('oContext', $oContext);
    }

    /**
     * Do the rest of init
     */
    public function doContextInit(GetResponseEvent $event)
    {
        /** @var $oContext \Context */
        $oContext = $event->getRequest()->attributes->get('oContext');
        $oContext->init();
    }

    public function checkModuleHandlerInit(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $oModuleHandler = new \ModuleHandler();
        $request->attributes->set('oModuleHandler', $oModuleHandler);
        if (!$oModuleHandler->init()) {
            $event->setResponse(new Response('Module handler init failure', 500));
        }
    }

    public function doContextCheckSSO(GetResponseEvent $event)
    {
        /** @var $oContext \Context */
        $oContext = $event->getRequest()->attributes->get('oContext');
        if (!$oContext->checkSSO()) {
            $event->setResponse(new Response('SSO?', 403));
        }
    }

    public function checkForErrorsAndPrepareMobileStatus(GetResponseEvent $event)
    {
        $oModuleHandler = $event->getRequest()->attributes->get('oModuleHandler');
        $oModuleHandler->checkForErrorsAndPrepareMobileStatus();

    }

    public function filterController(FilterControllerEvent $event)
    {
        /** @var $controller GlCMS\ControllerWrapper */
        $controller = $event->getController();
        $oModule = $controller->getModuleInstance();

        $oModuleHandler = $event->getRequest()->attributes->get('oModuleHandler');
        $oModuleHandler->filterController($oModule);

        // TODO Maybe setController after?
    }

    public function executeTriggersAddonsAndOthersBefore(FilterControllerEvent $event)
    {
        /** @var $controller GlCMS\ControllerWrapper */
        $controller = $event->getController();
        $oModule = $controller->getModuleInstance();

        $procResult = $oModule->preProc();
        if($procResult === false)
        {
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

    public function onKernelException(GetResponseForExceptionEvent $event)
    {

        //$exception = $event->getException();
        //$event->setResponse(new Response($exception->getMessage(), 500));
    }

    public function onTerminate(PostResponseEvent $event)
    {
        /** @var $oContext \Context */
        $oContext = $event->getRequest()->attributes->get('oContext');
        $oContext->close();
    }

    public function onView(GetResponseForControllerResultEvent $event)
    {
        /** @var $oModuleHandler \ModuleHandler */
        $oModuleHandler = $event->getRequest()->attributes->get('oModuleHandler');
        $response = $event->getControllerResult();
        $oModule = $response["oModule"];
        // $output = $response["output"];

        ob_start();
        $oModuleHandler->displayContent($oModule);
        $content = ob_get_clean();

        $event->setResponse(new Response($content));
    }

    public function prepareRequestForResolving(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $module_handler = $request->attributes->get('oModuleHandler');
        $request->attributes->set("act", $module_handler->act);
        $request->attributes->set("module", $module_handler->module);
        $request->attributes->set("is_mobile", \Mobile::isFromMobilePhone());
        $request->attributes->set("is_installed", \Context::isInstalled());
        $request->attributes->set('module_info', $module_handler->module_info);
    }

    public function routingListener(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $oContext = $request->attributes->get('oContext');
        // initialize the context that is also used by the generator (assuming matcher and generator share the same context instance)
        $this->context->fromRequest($request);
        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }
        // add attributes based on the request (routing)
        try {
            // matching a request is more powerful than matching a URL path + context, so try that first
            if ($this->matcher instanceof RequestMatcherInterface) {
                $parameters = $this->matcher->matchRequest($request);
            } else {
                $parameters = $this->matcher->match($request->getPathInfo());
            }
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], $this->parametersToString($parameters)));
            }

            // add route parameters to XE context as if they came from htaccess
            foreach ($parameters as $name=>$value) {
                if (!is_numeric($name)) {
                    $oContext->set($name, $value);
                }
            }

            $request->attributes->add($parameters);
            unset($parameters['_route']);
            unset($parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);

            //TODO solve circular reference for better integration?
            $oContext->set('request', $request);
        } catch (ResourceNotFoundException $e) {
            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());
            throw new NotFoundHttpException($message, $e);
        } catch (MethodNotAllowedException $e) {
            $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), strtoupper(implode(', ', $e->getAllowedMethods())));
            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        }
    }

}
