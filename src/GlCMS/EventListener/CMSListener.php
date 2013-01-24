<?php
namespace GlCMS\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
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

    public static function getSubscribedEvents()
    {
        return array(
            /*
             * Typical Purposes: To add more information to the Request, initialize parts of the system, or return a Response if possible (e.g. a security layer that denies access)
             * Overall, the purpose of the kernel.request event is either to create and return a Response directly, or to add information to the Request (e.g. setting the locale or setting some other information on the Request attributes).
             * Here we're adding the old Context model to the request attributes, dealing with routes, checking SSO and executing the old Context init
             * doContextGlobalsLink gets executed first, it has the biggest priority
             */
            KernelEvents::REQUEST => array(
                array('doContextGlobalsLink', 34),
                array('onKernelRequest', 32),
                //above this they could go together
                array('doContextInit', 30),
                array('doContextCheckSSO', 28),
                array('checkModuleHandlerInit', 26),
            ),
            KernelEvents::EXCEPTION => array('onKernelException', -128),
            KernelEvents::TERMINATE => 'onTerminate',
            KernelEvents::VIEW => 'onView'
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
        if ($response = $event->getControllerResult()) {
            if (is_string($response)) {
                $event->setResponse(new Response($response));
            }
        }
        else {
            $request = $event->getRequest();
            //... deal with controller returns
        }
    }

    public function onKernelRequest(GetResponseEvent $event)
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
