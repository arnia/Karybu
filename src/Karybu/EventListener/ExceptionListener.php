<?php

namespace Karybu\EventListener;

use Karybu\Exception\DBConnectionFailedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Karybu\EventListener\ExceptionHandler;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExceptionListener
 *
 * Catches all exceptions thrown by app
 * Returns 500 Server error HTTP Response and logs the exception
 *
 * @package Karybu\EventListener
 */
class ExceptionListener implements EventSubscriberInterface
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if($this->logger){
            $this->logger->error(sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine()));
        }

        if ($event->getKernel()->isDebug()) {
            $exception_handler = new ExceptionHandler(true);
            $response = $exception_handler->createResponse($exception);
            $event->setResponse($response);
            return;
        }

        if ($exception instanceof DBConnectionFailedException){
            // do nothing, subsequent calls will came to the same end
        }else{
            $status_code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : '500';
            // display content with message module instance
            $type = \Mobile::isFromMobilePhone() ? 'mobile' : 'view';
            /** @var $oMessageObject \messageView */
            $oMessageObject = \ModuleHandler::getModuleInstance('message', $type);
            $oMessageObject->setError(-1);
            $oMessageObject->setMessage(null);
            $oMessageObject->dispMessage();

            $module_handler = $event->getRequest()->attributes->get('oModuleHandler');
            $module_handler->_setHttpStatusMessage($status_code);
            $oMessageObject->setHttpStatusCode($status_code);
            $oMessageObject->setTemplateFile('http_status_code');

            $oDisplayHandler = new \DisplayHandler();
            $response = $oDisplayHandler->getReponseForModule($oMessageObject);

            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -128),
        );
    }
}
